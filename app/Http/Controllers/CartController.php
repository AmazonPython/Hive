<?php

namespace App\Http\Controllers;

use App\Models\ProductSku;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Http\Requests\AddCartRequest;

class CartController extends Controller
{
    protected $cartService;

    // 利用 Laravel 的自动解析功能注入 CartService 的实例
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    // 查看购物车
    public function index(Request $request)
    {
        $cartItems = $this->cartService->get();
        $addresses = $request->user()->addresses()->orderBy('last_used_at', 'desc')->get();

        // 获取总金额
        $totalAmount = 0;
        foreach($cartItems as $item) {
            if ($item->productSku->product->on_sale) {
                $totalAmount += $item->productSku->price * $item->amount;
            }
        }

        return view('cart.index', compact('cartItems', 'addresses', 'totalAmount'));
    }

    // 将商品添加到购物车
    public function add(AddCartRequest $request)
    {
        // 创建一个新的购物车记录
        $this->cartService->add($request->input('sku_id'), $request->input('amount'));

        return [];
    }

    // 移除购物车商品
    public function remove(ProductSku $sku, Request $request)
    {
        // 删除对应的购物车商品
        $this->cartService->remove($sku->id);

        return [];
    }
}
