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
// 欢迎页
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

Auth::routes(['verify' => true]);
// 路由跳转
Route::redirect('/', '/products')->name('root');

Route::group(['prefix' => 'products'], function () {
    // 商品列表页
    Route::get('/', 'ProductsController@index')->name('products.index');
    // 商品详情页
    // restful 查看，product仅支持数整，当id规则变更后，只需更改正则条件
    Route::get('{product}', 'ProductsController@show')->name('products.show')->where('product', '[0-9]+');
});

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::group(['prefix' => 'user_addresses'], function () {
        // 用户收货地址
        Route::get('/', 'UserAddressesController@index')->name('user_addresses.index');
        // 新增收货地址
        Route::get('create', 'UserAddressesController@create')->name('user_addresses.create');
        // 保存收货地址
        Route::post('/', 'UserAddressesController@store')->name('user_addresses.store');
        // 编辑收货地址
        Route::get('{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
        // 更新收货地址
        Route::put('{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
        // 删除收货地址
        Route::delete('{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');
    });

    Route::group(['prefix' => 'products'], function () {
        // 收藏商品与取消收藏
        Route::post('{product}/favorite', 'ProductsController@favor')->name('products.favor');
        Route::delete('{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
        // 收藏商品列表
        Route::get('favorites/page/{id}', 'ProductsController@favorites')->name('products.favorites');
    });

    Route::group(['prefix' => 'cart'], function () {
        // 添加购物车
        Route::post('/', 'CartController@add')->name('cart.add');
        // 购物车列表
        Route::get('/', 'CartController@index')->name('cart.index');
        // 删除购物车
        Route::delete('{sku}', 'CartController@remove')->name('cart.remove');
    });

    Route::group(['prefix' => 'orders'], function () {
        // 订单结算
        Route::post('/', 'OrdersController@store')->name('orders.store');
        // 订单列表
        Route::get('page/{id}', 'OrdersController@index')->name('orders.index');
        // 订单详情
        Route::get('{order}', 'OrdersController@show')->name('orders.show');
        // 确认收货
        Route::post('{order}/received', 'OrdersController@received')->name('orders.received');
        // 评分页面
        Route::get('{order}/review', 'OrdersController@review')->name('orders.review.show');
        // 填写评分
        Route::post('{order}/review', 'OrdersController@sendReview')->name('orders.review.store');
        // 退款
        Route::post('{order}/apply_refund', 'OrdersController@applyRefund')->name('orders.apply_refund');
    });

    Route::group(['prefix' => 'payment'], function () {
        // 支付宝支付
        Route::get('{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
        // 前端回调
        Route::get('alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
        // 微信支付
        Route::get('{order}/wechat', 'PaymentController@payByWechat')->name('payment.wechat');
        // 分期付款
        Route::post('{order}/installment', 'PaymentController@payByInstallment')->name('payment.installment');
    });

    Route::group(['prefix' => 'installments'], function () {
        // 查看分期付款计划
        Route::get('/', 'InstallmentsController@index')->name('installments.index');
        // 分期付款详情页
        Route::get('{installment}', 'InstallmentsController@show')->name('installments.show');
        // 分期付款支付宝支付
        Route::get('{installment}/alipay', 'InstallmentsController@payByAlipay')->name('installments.alipay');
        // 分期付款支付前端同步回调
        Route::get('alipay/return', 'InstallmentsController@alipayReturn')->name('installments.alipay.return');
        // 分期付款微信支付
        Route::get('{installment}/wechat', 'InstallmentsController@payByWechat')->name('installments.wechat');
    });

    // 优惠码列表
    Route::get('coupon_codes/{code}', 'CouponCodesController@show')->name('coupon_codes.show');

    // 众筹商品下单
    Route::post('crowdfunding_orders', 'OrdersController@crowdfunding')->name('crowdfunding_orders.store');
});

Route::group(['prefix' => 'payment'], function () {
    // 支付宝异步通知
    Route::post('alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');
    // 微信支付异步通知
    Route::post('wechat/notify', 'PaymentController@wechatNotify')->name('payment.wechat.notify');
    // 微信支付退款异步通知
    Route::post('wechat/refund_notify', 'PaymentController@wechatRefundNotify')->name('payment.wechat.refund_notify');
});

Route::group(['prefix' => 'installments'], function () {
    // 分期付款异步通知
    Route::post('alipay/notify', 'InstallmentsController@alipayNotify')->name('installments.alipay.notify');
    // 分期付款微信支付异步通知
    Route::post('wechat/notify', 'InstallmentsController@wechatNotify')->name('installments.wechat.notify');
    // 分期付款微信退款异步通知
    Route::post('wechat/refund_notify', 'InstallmentsController@wechatRefundNotify')->name('installments.wechat.refund_notify');
});
