@extends('layouts.app')
@section('title', '订单列表')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    {{ Auth::user()->name }} 的订单<a class="products-orders-counts ms-4">共 {{ $total }}条</a>
                </div>
                <div class="card-body">
                    @if($orders->count() === 0)
                        <div class="empty-block mb-2">
                            没有查询到相关订单，<a class="btn btn-sm btn-info" href="{{ route('products.index') }}">去逛逛吧 <i class="fa fa-shopping-cart"></i></a>
                        </div>
                        <img src="https://thiscatdoesnotexist.com/" width="50%" alt="404 not found">
                    @else
                    <ul class="list-group">
                        @foreach($orders as $order)
                            <li class="list-group-item">
                                <div class="card">
                                    <div class="card-header">
                                        订单号：{{ $order->no }}
                                        <span class="float-end">时间：{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
                                    </div>
                                    <div class="card-body">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>商品信息</th>
                                                <th class="text-center">单价</th>
                                                <th class="text-center">数量</th>
                                                <th class="text-center">订单总价</th>
                                                <th class="text-center">状态</th>
                                                <th class="text-center">操作</th>
                                            </tr>
                                            </thead>
                                            @foreach($order->items as $index => $item)
                                                <tr>
                                                    <td class="product-info">
                                                        <div class="preview">
                                                            <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                                                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->title }}">
                                                            </a>
                                                        </div>
                                                        <div>
                                                            <span class="product-title">
                                                               <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                                                                   {{ $item->product->title }}
                                                               </a>
                                                            </span>
                                                            <span class="sku-title">{{ $item->productSku->title }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="sku-price text-center">￥{{ $item->price }}</td>
                                                    <td class="sku-amount text-center">{{ $item->amount }}</td>
                                                    @if($index === 0)
                                                        <td rowspan="{{ count($order->items) }}" class="text-center total-amount">
                                                            ￥{{ $order->total_amount }}
                                                        </td>
                                                        <td rowspan="{{ count($order->items) }}" class="text-center">
                                                            @if($order->paid_at)
                                                                @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                                                    已支付
                                                                @else
                                                                    {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                                                                @endif
                                                            @elseif($order->closed)
                                                                已关闭
                                                            @else
                                                                未支付<br>
                                                                请于 {{ $order->created_at->addSeconds(config('app.order_ttl'))->format('H:i') }} 前完成支付<br>
                                                                否则订单将自动关闭
                                                            @endif
                                                        </td>
                                                        <td rowspan="{{ count($order->items) }}" class="text-center">
                                                            <a class="btn btn-primary btn-sm" href="{{ route('orders.show', ['order' => $order->id]) }}">查看订单</a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <!-- 分页 -->
                    <div class="pagination float-start">
                        @if($prevUrl)
                            <a class="btn btn-info" href="{{ route('orders.index', 1) }}">首页</a>
                            <a class="btn btn-info ms-2" href="{{ route('orders.index', $prevUrl) }}"><i class="fa fa-angle-left" aria-hidden="true"></i> 上一页</a>
                        @endif
                    </div>
                    <div class="pagination float-end">
                        @if($nextUrl)
                            <a class="btn btn-info me-2" href="{{ route('orders.index', $nextUrl) }}">下一页 <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                        @endif
                        <a class="btn btn-info" href="{{ route('orders.index', $lastUrl) }}">末页</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
