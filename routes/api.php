<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'App\Http\Controllers\Api\v1'], function () {

    // 门面测试
    Route::any('v1/demo/facadeService','DemoController@facadeService');

    Route::any('v1/demo/index','DemoController@index');
    // lock
    Route::any('v1/demo/lock','DemoController@lock');
    // 雪花生成器
    Route::any('v1/demo/snowflake','DemoController@snowflake');
    // 登录
    Route::get('v1/login/getToken','LoginController@getToken');
    Route::get('v1/login/verifyToken','LoginController@verifyToken');
    // 分类
    Route::get('v1/category/listAll','CategoryController@listAll');
    // 商品
    Route::get('v1/goods/detail','GoodsController@detail');

    // 秒杀
    Route::get('v1/secKill/sharedLock','SecKillController@sharedLock');
    Route::get('v1/secKill/exclusiveLock','SecKillController@exclusiveLock');
    Route::get('v1/secKill/redisLock','SecKillController@redisLock');

    Route::group(['middleware' => ['checkToken']], function () {
        Route::get('v1/user/getUser','UserController@getUser');
        Route::get('v1/user/updateUser','UserController@updateUser');
        // 购物车
        Route::post('v1/cart/add','CartController@add');
        Route::post('v1/cart/update','CartController@update');
        Route::post('v1/cart/delete','CartController@delete');
        Route::get('v1/cart/list','CartController@list');
        Route::get('v1/cart/single','CartController@single'); // service方法供下单使用
        // 收货地址
        Route::get('v1/userAddress/list','UserAddressController@list');
        Route::post('v1/userAddress/add','UserAddressController@add');
        Route::post('v1/userAddress/update','UserAddressController@update');
        Route::post('v1/userAddress/delete','UserAddressController@delete');
        // 订单
        Route::post('v1/order/add','OrderController@add');
        Route::post('v1/order/addSingle','OrderController@addSingle');
        Route::get('v1/order/zAdd','OrderController@zAdd');
        Route::get('v1/order/checkOrderStatus','OrderController@checkOrderStatus');

    });
});



