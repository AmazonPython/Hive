<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\UserAddress;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;

class OrdersController extends Controller
{
    // 查看订单列表
    public function index(Request $request)
    {
        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    // 查看订单详情
    public function show(Order $order)
    {
        $this->authorize('own', $order);

        // 延迟加载，避免N + 1问题
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    // 创建订单
    // 利用 Laravel 的自动解析功能注入 OrderService 类
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
    }
}
