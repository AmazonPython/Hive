<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\CrowdfundingProduct;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// ShouldQueue 代表此任务需要异步执行
class RefundCrowdfundingOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $crowdfunding;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CrowdfundingProduct $crowdfunding)
    {
        $this->crowdfunding = $crowdfunding;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 如果众筹的状态不是失败则不执行退款，原则上不会发生，这里只是增加健壮性
        if ($this->crowdfunding->status !== CrowdfundingProduct::STATUS_FAIL) {
            return;
        }

        // 将定时任务中的众筹失败退款代码移到这里
        $orderService = app(OrderService::class);

        // 查询出所有参与了此众筹的订单
        Order::query()
            ->where('type', Order::TYPE_CROWDFUNDING) // 订单类型为众筹商品订单
            ->whereNotNull('paid_at') // 已支付的订单
            ->whereHas('items', function ($query) { // 包含了当前商品的订单
                $query->where('product_id', $this->crowdfunding->product_id); // 当前商品
            })
            ->get()
            ->each(function (Order $order) use ($orderService) {
                $orderService->refundOrder($order); // 调用退款逻辑
            });
    }
}
