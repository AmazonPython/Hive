<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 从数据库中随机获取一个商品
        $product = Product::query()->where('on_sale', true)->inRandomOrder()->first();

        // 从该商品中随机获取一个 SKU
        $sku = $product->skus()->inRandomOrder()->first();

        return [
            'amount'         => random_int(1, 3), // 随机购买数量
            'price'          => $sku->price,
            'total_amount'   => $sku->price * random_int(1, 3),
            'rating'         => null,
            'review'         => null,
            'reviewed_at'    => null,
            'product_id'     => $product->id,
            'product_sku_id' => $sku->id,
        ];
    }
}
