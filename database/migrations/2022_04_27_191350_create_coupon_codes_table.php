<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_codes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->comment('优惠码');
            $table->string('type')->comment('优惠类型');
            $table->decimal('value')->comment('优惠值');
            $table->unsignedInteger('total')->comment('发行总量');
            $table->unsignedInteger('used')->default(0)->comment('已使用数量');
            $table->decimal('min_amount', 10, 2)->comment('最低消费金额');
            $table->dateTime('not_before')->nullable()->comment('截至时间');
            $table->dateTime('not_after')->nullable()->comment('有效期');
            $table->boolean('enabled')->comment('是否启用');
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
        Schema::dropIfExists('coupon_codes');
    }
}
