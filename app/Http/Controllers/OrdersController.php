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
    public function index($id, Request $request)
    {
        // 设置自定义分页页面信息
        $paginate = 5; // 每页显示数量
        $skip = ($id*$paginate)-$paginate; // 跳过数量
        $prevUrl = $nextUrl = ''; // 分页链接
        if($skip > 0){ // 如果跳过数量大于0，则设置上一页链接
            $prevUrl = $id - 1;
        }

        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->skip($skip)->take($paginate)->get();

        if($orders->count() > 0){ // 如果订单数量大于0，则设置下一页链接
            if($orders->count() >= $paginate){
                $nextUrl = $id + 1;
            }

            return view('orders.index', compact('orders', 'prevUrl', 'nextUrl'));
        }

        return view('orders.index', compact('orders', 'prevUrl', 'nextUrl'));
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
