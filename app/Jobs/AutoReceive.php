<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// 代表这个类需要被放到队列中执行，而不是触发时立即执行
class AutoReceive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $delay)
    {
        $this->order = $order;

        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    /*
     * 定义这个任务类具体的执行逻辑
     * 当队列处理器从队列中取出任务时，会调用 handle() 方法
     */
    public function handle()
    {
        // 判断对应的订单是否已经被收货
        // 如果已经收货则不需要确认收货，直接退出
        if ($this->order->ship_status === Order::SHIP_STATUS_RECEIVED) {
            return;
        }
        // 通过事务执行 sql
        \DB::transaction(function () {
            // 更新订单的状态改为已收货
            $this->order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);
        });
    }
}
