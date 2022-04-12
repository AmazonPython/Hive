@extends('layouts.app')
@section('title', '我的收藏')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    {{ Auth::user()->name }} 的收藏<a class="products-favorites-counts ms-4">共 {{ $total }}件</a>
                </div>
                <div class="card-body">
                    @if($products->count() === 0)
                        <div class="empty-block mb-2">
                            您还没有收藏任何商品，赶紧去 <a class="btn btn-sm btn-info" href="{{ route('products.index') }}">浏览商品<i class="fa fa-shopping-cart"></i></a> 吧
                        </div>
                        <img src="https://thiscatdoesnotexist.com/" width="50%" alt="404 not found">
                    @else
                        <div class="row products-list">
                            @foreach($products as $product)
                                <div class="col-3 product-item">
                                <div class="product-content">
                                    <div class="top">
                                        <div class="img">
                                            <a href="{{ route('products.show', ['product' => $product->id]) }}">
                                                <img src="{{ $product->image_url }}" alt="{{ $product->title }}">
                                            </a>
                                        </div>
                                        <div class="price"><b>￥</b>{{ $product->price }}</div>
                                        <a href="{{ route('products.show', ['product' => $product->id]) }}">
                                            {{ $product->title }}
                                        </a>
                                    </div>
                                    <div class="bottom">
                                        <div class="sold_count">销量 <span>{{ $product->sold_count }}笔</span></div>
                                        <div class="review_count">评价 <span>{{ $product->review_count }}</span></div>
                                    </div>
                                </div>
                                </div>
                            @endforeach
                        </div>
                        <!-- 分页 -->
                        <div class="pagination float-start">
                            @if($prevUrl)
                                <a class="btn btn-info" href="{{ route('products.favorites', 1) }}">首页</a>
                                <a class="btn btn-info ms-2" href="{{ route('products.favorites', $prevUrl) }}"><i class="fa fa-angle-left" aria-hidden="true"></i> 上一页</a>
                            @endif
                        </div>
                        <div class="pagination float-end">
                            @if($nextUrl)
                                <a class="btn btn-info me-2" href="{{ route('products.favorites', $nextUrl) }}">下一页 <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                            @endif
                            <a class="btn btn-info" href="{{ route('products.favorites', $lastUrl) }}">末页</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
