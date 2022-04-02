<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use App\Http\Requests\AddCartRequest;

class CartController extends Controller
{
    // 查看购物车
    public function index(Request $request)
    {
        // 预加载获取购物车中的商品信息，避免重复查询
        $cartItems = $request->user()->cartItems()->with(['productSku.product'])->get();

        return view('cart.index', compact('cartItems'));
    }

    // 将商品添加到购物车
    public function add(AddCartRequest $request)
    {
        $user   = $request->user();
        $skuId  = $request->input('sku_id');
        $amount = $request->input('amount');

        // 从数据库中查询该商品是否已经在购物车中
        if ($cart = $user->cartItems()->where('product_sku_id', $skuId)->first()) {

            // 如果存在则直接叠加商品数量
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);
        } else {

            // 否则创建一个新的购物车记录
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($skuId);
            $cart->save();
        }

        return [];
    }

    // 移除购物车商品
    public function remove(ProductSku $sku, Request $request)
    {
        $request->user()->cartItems()->where('product_sku_id', $sku->id)->delete();

        return [];
    }
}
