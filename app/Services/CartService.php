<?php

namespace App\Services;

use Auth;
use App\Models\CartItem;

// 封装购物车业务代码，只要把业务代码封装到这个类中即可
class CartService
{
    public function get()
    {
        // 预加载获取购物车中的商品信息，避免重复查询
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }

    public function add($skuId, $amount)
    {
        $user = Auth::user();

        // 从数据库中查询该商品是否已经在购物车中
        if ($item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {

            // 如果存在则直接叠加商品数量
            $item->update([
                'amount' => $item->amount + $amount,
            ]);
        } else {

            // 否则创建一个新的购物车记录
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($skuId);
            $item->save();
        }

        return $item;
    }

    public function remove($skuIds)
    {
        // 可以传单个 ID，也可以传 ID 数组
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }

        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }
}
