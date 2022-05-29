<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallmentItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('installment_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('installment_id')->comment('分期 ID');
            $table->foreign('installment_id')->references('id')->on('installments')->onDelete('cascade');
            $table->unsignedInteger('sequence')->comment('分期还款顺序');
            $table->decimal('base')->comment('当期本金');
            $table->decimal('fee')->comment('当期手续费');
            $table->decimal('fine')->nullable()->comment('当期罚息');
            $table->dateTime('due_date')->comment('到期日');
            $table->dateTime('paid_at')->nullable()->comment('还款日');
            $table->string('payment_method')->nullable()->comment('还款方式');
            $table->string('payment_no')->nullable()->comment('还款订单号');
            $table->string('refund_status')->default(\App\Models\InstallmentItem::REFUND_STATUS_PENDING)->comment('退款状态');
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
        Schema::dropIfExists('installment_items');
    }
}
