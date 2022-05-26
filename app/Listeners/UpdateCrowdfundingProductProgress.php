<?php

namespace App\Listeners;

use App\Models\Order;
use App\Events\OrderPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCrowdfundingProductProgress implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        $order = $event->getOrder();

        // 如果订单类型不是众筹商品订单，则不处理
        if ($order->type !== Order::TYPE_CROWDFUNDING) {
            return;
        }

        $crowdfunding = $order->items[0]->product->crowdfunding;

        $date = Order::query()
            ->where('type', Order::TYPE_CROWDFUNDING) // 查出所有众筹商品订单
            ->whereNotNull('paid_at') // 并且已经支付
            ->whereHas('items', function ($query) use ($crowdfunding) {
                $query->where('product_id', $crowdfunding->product_id); // 并且是当前众筹商品
            })
            ->first([
                \DB::raw('sum(total_amount) as total_amount'), // 取出总金额
                \DB::raw('count(distinct(users.id)) as user_count'), // 取出去重的用户数量
            ]);

        // 更新众筹商品的进度
        $crowdfunding->update([
            'total_amount' => $date->total_amount,
            'user_count' => $date->user_count,
        ]);
    }
}
