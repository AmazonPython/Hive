<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use App\Events\OrderPaid;
use App\Models\Installment;
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;

class InstallmentsController extends Controller
{
    // 获取分期付款列表
    public function index(Request $request)
    {
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);

        return view('installments.index', compact('installments'));
    }

    // 获取分期付款详情
    public function show(Installment $installment)
    {
        // 校验是否属于当前用户
        $this->authorize('own', $installment);

        // 获取分期付款对应的商品，并按照分期期数排序
        $items = $installment->items()->orderBy('sequence')->get();

        // 下一个未完成的还款计划
        $nextItem = $items->where('sequence', $installment->next_sequence)->first();

        return view('installments.show', compact('installment', 'items', 'nextItem'));
    }

    // 分期付款支付宝还款
    public function payByAlipay(Installment $installment)
    {
        if ($installment->order->closed) {
            throw new InvalidRequestException('对应的商品订单已被关闭');
        }

        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期订单已结清，无需支付');
        }

        // 获取当前分期付款最近的一个未支付的还款计划
        if (!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()) {
            throw new InvalidRequestException('该分期订单已结清，无需支付');
        }

        // 调用支付宝的网页支付
        return app('alipay')->web([
            'out_trade_no' => $installment->no . '_' . $nextItem->sequence, // 账单编号，需保证在商户端不重复
            'total_amount' => $nextItem->total, // 账单金额，单位元，支持小数点后两位
            'subject'      => '支付 Hive Store 的分期费用：' . $installment->no, // 账单标题
            /*
             * 由于分期还款的支付回调逻辑与普通商品订单支付回调逻辑是不一样的，需要单独处理
             * 这里的 notify_url 和 return_url 可以覆盖掉在 AppServiceProvider 设置的回调地址
             */
            'notify_url'   => ngrok_url('installments.alipay.notify'),
            'return_url'   => route('installments.alipay.return'),
        ]);
    }

    // 分期付款支付宝还款的支付宝前端回调
    public function alipayReturn()
    {
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }

    // 分期付款支付宝还款的支付宝后端回调
    public function alipayNotify()
    {
        // 校验支付宝回调参数是否正确
        $data = app('alipay')->verify();

        // 如果订单状态不是成功或者结束，则不走后续的逻辑
        if (!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }

        // 调整原本的支付宝回调，改为调用 paid 方法
        if ($this->paid($data->out_trade_no, 'alipay', $data->trade_no)) {
            return app('alipay')->success();
        }

        return 'fail';
    }

    // 分期付款微信还款
    public function payByWechat(Installment $installment)
    {
        if ($installment->order->closed) {
            throw new InvalidRequestException('对应的商品订单已被关闭');
        }

        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期订单已结清');
        }

        if (!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()) {
            throw new InvalidRequestException('该分期订单已结清');
        }

        $wechatOrder = app('wechat_pay')->scan([
            'out_trade_no' => $installment->no . '_' . $nextItem->sequence,
            'total_fee'    => $nextItem->total * 100,
            'body'         => '支付 Hive Store 的分期订单：' . $installment->no,
            'notify_url'   => ngrok_url('installments.wechat.notify'),
        ]);
        // 把要转换的字符串作为 QrCode 的构造函数参数
        $qrCode = new QrCode($wechatOrder->code_url);

        // 将生成的二维码图片数据以字符串形式输出，并带上相应的响应类型
        return response($qrCode->writeString(), 200, ['Content-Type' => $qrCode->getContentType()]);
    }

    // 分期付款支付宝还款的微信后端回调
    public function wechatNotify()
    {
        $data = app('wechat_pay')->verify();

        if ($this->paid($data->out_trade_no, 'wechat', $data->transaction_id)) {
            return app('wechat_pay')->success();
        }

        return 'fail';
    }

    protected function paid($outTradeNo, $paymentMethod, $paymentNo)
    {
        // 拉起支付时使用的支付订单号是由分期流水号 + 还款计划编号组成的
        // 因此可以通过支付订单号来还原出这笔还款是哪个分期付款对应的哪个还款计划
        list($no, $sequence) = explode('_', $outTradeNo);

        // 根据分期流水号查询对应的分期记录，原则上不会找不到，这里的判断只是增强代码健壮性
        if (!$installment = Installment::where('no', $no)->first()) {
            return false;
        }

        // 根据还款计划编号查询对应的还款计划，原则上这里的判断只是增强代码健壮性
        if (!$item = $installment->items()->where('sequence', $sequence)->first()) {
            return false;
        }

        // 如果这个还款计划的支付状态是已支付，则告知支付宝该订单已完成，不再执行后续逻辑
        if ($item->paid_at) {
            return true;
        }

        // 使用事务，保持数据一致性
        \DB::transaction(function () use ($paymentNo, $paymentMethod, $no, $installment, $item) {
            // 更新对应的还款计划
            $item->update([
                'paid_at' => Carbon::now(), // 支付时间
                'payment_method' => $paymentMethod, // 支付方式
                'payment_no'     => $paymentNo, // 支付订单号
            ]);

            // 如果这是第一笔还款
            if ($item->sequence === 0) {
                // 把分期状态改为还款中
                $installment->update(['status' => Installment::STATUS_REPAYING]);
                // 将分期付款订单对应的商品订单状态改为已支付
                $installment->order->update([
                    'paid_at' => Carbon::now(),
                    'payment_method' => 'installment', // 支付方式为分期付款
                    'payment_no' => $no, // 支付订单号为分期付款的流水号
                ]);
                // 触发商品已支付事件
                event(new OrderPaid($installment->order));
            }

            // 如果这是最后一笔还款
            if ($item->sequence === $installment->count - 1) {
                // 将分期付款状态改为已结清
                $installment->update(['status' => Installment::STATUS_FINISHED]);
            }
        });

        return true;
    }
}
