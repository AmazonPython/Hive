<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('no')->unique()->comment('订单号');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('address')->comment('订单地址');
            $table->decimal('total_amount', 10, 2)->comment('总金额');
            $table->string('remark')->nullable()->comment('订单备注');
            $table->dateTime('paid_at')->nullable()->comment('支付时间');
            $table->string('payment_method')->nullable()->comment('支付方式');
            $table->string('payment_no')->nullable()->comment('支付平台订单号');
            $table->string('refund_status')->default(\App\Models\Order::REFUND_STATUS_PENDING)->comment('退款状态');
            $table->string('refund_no')->unique()->nullable()->comment('退款单号');
            $table->boolean('closed')->default(false)->comment('订单是否关闭');
            $table->boolean('reviewed')->default(false)->comment('订单是否评价');
            $table->string('ship_status')->default(\App\Models\Order::SHIP_STATUS_PENDING)->comment('发货状态');
            $table->text('ship_data')->nullable()->comment('物流信息');
            $table->text('extra')->nullable()->comment('订单扩展信息');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
