<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);

        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量。search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        // 是否有提交 order 参数，如果有就赋值给 $order 变量。order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $products = $builder->paginate(16);

        return view('products.index', [
            'products' => $products,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
        ]);
    }

    public function show(Product $product, Request $request)
    {
        // 判断商品是否已经上架，如果没有上架则抛出异常。
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }

        $favored = false;

        // 用户未登录时返回的是 null，已登录时返回的是对应的用户对象
        if($user = $request->user()) {
            // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品
            // boolval() 函数用于把值转为布尔值
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();

        return view('products.show', compact('product', 'favored', 'reviews'));
    }

    // 收藏商品
    public function favor(Product $product, Request $request)
    {
        $user = $request->user();

        // 判断这个商品是否已经被当前用户收藏
        if ($user->favoriteProducts()->find($product->id)) {
            // 如果已经收藏则不做任何操作直接返回
            return [];
        }
        // 如果没有收藏过则收藏
        $user->favoriteProducts()->attach($product);

        return [];
    }

    // 取消收藏
    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();

        // 判断这个商品是否已经被当前用户收藏
        if (!$user->favoriteProducts()->find($product->id)) {
            // 如果没有收藏则不做任何操作直接返回
            return [];
        }
        // 如果收藏过则取消收藏
        $user->favoriteProducts()->detach($product);

        return [];
    }

    // 商品收藏列表页面
    public function favorites($id, Request $request)
    {
        // 设置自定义分页页面信息
        $paginate = 2; // 每页显示数量
        $skip = ($id * $paginate) - $paginate; // 跳过数量
        $prevUrl = $nextUrl = ''; // 分页链接
        if($skip > 0){ // 如果跳过数量大于0，则设置上一页链接
            $prevUrl = $id - 1;
        }
        // 收藏物品总数
        $total = $request->user()->favoriteProducts()->count();
        // 尾页页码
        $lastUrl = ceil($request->user()->favoriteProducts()->count() / $paginate);

        $products = $request->user()->favoriteProducts()->skip($skip)->take($paginate)->get();

        if($products->count() > 0){ // 如果订单数量大于0，则设置下一页链接
            if($products->count() >= $paginate){// 如果订单数量大于等于每页显示数量，则设置下一页链接
                // 如果当前页码小于最后一页，则设置下一页链接
                if($id < $lastUrl){
                    $nextUrl = $id + 1;
                }
            }

            return view('products.favorites', compact('products', 'prevUrl', 'nextUrl', 'lastUrl', 'total'));
        }

        return view('products.favorites', compact('products', 'prevUrl', 'nextUrl', 'lastUrl', 'total'));
    }
}
