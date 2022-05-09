<?php

namespace Database\Factories;

use App\Models\CouponCode;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 随机取一个用户
        $user = User::query()->inRandomOrder()->first();

        // 随机取一个该用户的地址
        $address = $user->addresses()->inRandomOrder()->first();

        // 10% 的概率把订单设置为已退款
        $refund = random_int(0, 10) < 1;

        // 随机生成发货状态
        $ship = $this->faker->randomElement(array_keys(Order::$shipStatusMap));

        // 优惠券
        $coupon = null;

        // 30% 的概率使用优惠券
        if (random_int(0, 10) < 3) {
            // 为了避免逻辑错误，只选择没有最低消费金额的优惠券
            $coupon = CouponCode::query()->where('min_amount', 0)->inRandomOrder()->first();
            // 增加优惠券的使用量
            $coupon->changeUsed();
        }

        return [
            'address'        => [
                'address'       => $address->full_address,
                'zip'           => $address->zip,
                'contact_name'  => $address->contact_name,
                'contact_phone' => $address->contact_phone,
            ],
            'total_amount'   => 0,
            'remark'         => $this->faker->sentence,
            'paid_at'        => $this->faker->dateTimeBetween('-30 days'), // 30天前到现在任意时间点
            'payment_method' => $this->faker->randomElement(['wechat', 'alipay']),
            'payment_no'     => $this->faker->uuid,
            'refund_status'  => $refund ? Order::REFUND_STATUS_SUCCESS : Order::REFUND_STATUS_PENDING,
            'refund_no'      => $refund ? Order::getAvailableRefundNo() : null,
            'closed'         => false,
            'reviewed'       => random_int(0, 10) > 2,
            'ship_status'    => $ship,
            'ship_data'      => $ship === Order::SHIP_STATUS_PENDING ? null : [
                'express_company' => $this->faker->company,
                'express_no'      => $this->faker->uuid,
            ],
            'extra'          => $refund ? ['refund_reason' => $this->faker->sentence] : [],
            'user_id'        => $user->id,
            'coupon_code_id' => $coupon ? $coupon->id : null,
        ];
    }
}
