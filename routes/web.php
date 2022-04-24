<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['verify' => true]);
// 路由跳转
Route::redirect('/', '/products')->name('root');

// 商品列表页
Route::get('products', 'ProductsController@index')->name('products.index');
// 商品详情页
// restful 查看，product仅支持数整，当id规则变更后，只需更改正则条件
Route::get('products/{product}', 'ProductsController@show')->name('products.show')->where('product', '[0-9]+');

Route::group(['middleware' => ['auth', 'verified']], function () {
    // 用户收货地址
    Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
    // 新增收货地址
    Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
    // 保存收货地址
    Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
    // 编辑收货地址
    Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
    // 更新收货地址
    Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
    // 删除收货地址
    Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');

    // 收藏商品与取消收藏
    Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
    Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
    // 收藏商品列表
    Route::get('products/favorites/page/{id}', 'ProductsController@favorites')->name('products.favorites');

    // 添加购物车
    Route::post('cart', 'CartController@add')->name('cart.add');
    // 购物车列表
    Route::get('cart', 'CartController@index')->name('cart.index');
    // 删除购物车
    Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');

    // 订单结算
    Route::post('orders', 'OrdersController@store')->name('orders.store');
    // 订单列表
    Route::get('orders/page/{id}', 'OrdersController@index')->name('orders.index');
    // 订单详情
    Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');

    // 支付宝支付
    Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
    // 前端回调
    Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');

    // 微信支付
    Route::get('payment/{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');

    // 确认收货
    Route::post('orders/{order}/received', 'OrdersController@received')->name('orders.received');

    // 评分页面
    Route::get('orders/{order}/review', 'OrdersController@review')->name('orders.review.show');

    // 填写评分
    Route::post('orders/{order}/review', 'OrdersController@sendReview')->name('orders.review.store');

    // 退款
    Route::post('orders/{order}/apply_refund', 'OrdersController@applyRefund')->name('orders.apply_refund');
});

// 支付宝异步通知
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');

// 微信支付异步通知
Route::post('payment/wechat/notify', 'PaymentController@wechatNotify')->name('payment.wechat.notify');
