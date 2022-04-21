<?php

namespace App\Http\Controllers;

use App\Events\OrderReviewed;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\SendReviewRequest;
use App\Models\UserAddress;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\OrderService;

class OrdersController extends Controller
{
    // 查看订单列表
    public function index($id, Request $request)
    {
        // 设置自定义分页页面信息
        $paginate = 5; // 每页显示数量
        $skip = ($id * $paginate) - $paginate; // 跳过数量
        $prevUrl = $nextUrl = ''; // 分页链接
        if($skip > 0){ // 如果跳过数量大于0，则设置上一页链接
            $prevUrl = $id - 1;
        }
        // 尾页页码
        $lastUrl = ceil(Order::query()->where('user_id', $request->user()->id)->count() / $paginate);
        // 订单总数
        $total = Order::query()->where('user_id', $request->user()->id)->count();

        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->skip($skip)->take($paginate)->get();

        if($orders->count() > 0){ // 如果订单数量大于0，则设置下一页链接
            if($orders->count() >= $paginate){// 如果订单数量大于等于每页显示数量，则设置下一页链接
                // 如果当前页码小于最后一页，则设置下一页链接
                if($id < $lastUrl){
                    $nextUrl = $id + 1;
                }
            }

            return view('orders.index', compact('orders', 'prevUrl', 'nextUrl', 'lastUrl', 'total'));
        }

        return view('orders.index', compact('orders', 'prevUrl', 'nextUrl', 'lastUrl', 'total'));
    }

    // 查看订单详情
    public function show(Order $order)
    {
        $this->authorize('own', $order);

        // 延迟加载，避免N + 1问题
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    // 创建订单
    // 利用 Laravel 的自动解析功能注入 OrderService 类
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
    }

    // 客户端确认收货
    public function received(Order $order)
    {
        // 校验权限
        $this->authorize('own', $order);

        // 判断订单的发货状态是否为已发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }

        // 更新发货状态为已收到
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        // 返回订单信息
        return $order;
    }

    // 评分
    public function review(Order $order)
    {
        // 校验权限
        $this->authorize('own', $order);

        // 判断是否已经支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }

        // 使用 load 方法加载关联数据，避免 N + 1 性能问题
        return view('orders.review', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    // 保存评分
    public function sendReview(Order $order, SendReviewRequest $request)
    {
        // 校验权限
        $this->authorize('own', $order);

        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }

        // 判断是否已经评价
        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已评价，不可重复提交');
        }

        $reviews = $request->input('reviews');
        // 开启事务
        \DB::transaction(function () use ($reviews, $order) {
            // 遍历用户提交的数据
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);

                // 保存评分和评价
                $orderItem->update([
                    'rating'      => $review['rating'],
                    'review'      => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
            }

            // 将订单标记为已评价
            $order->update(['reviewed' => true]);

            event(new OrderReviewed($order));
        });

        return redirect()->back();
    }
}
