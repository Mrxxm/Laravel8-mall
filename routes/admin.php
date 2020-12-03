<?php

Route::group(['namespace' => 'App\Http\Controllers\Admin'], function () {

    Route::get('/login/getAppToken','LoginController@getAppToken');

    Route::group(['middleware' => ['checkAdminToken']], function () {

        Route::get('/login/logout','LoginController@logout');
        // 后台用户管理
        Route::get('/adminUser/list','AdminUserController@list');
        Route::post('/adminUser/add','AdminUserController@add');
        Route::post('/adminUser/update','AdminUserController@update');
        Route::post('/adminUser/delete','AdminUserController@delete');
        // 前台用户管理
        Route::get('/user/list','UserController@list');
        // 分类管理
        Route::get('/category/search','CategoryController@search');
        Route::get('/category/list','CategoryController@list');
        Route::post('/category/add','CategoryController@add');
        Route::post('/category/update','CategoryController@update');
        Route::post('/category/delete','CategoryController@delete');
        // 规格管理
        Route::get('/specs/search','SpecsController@search');
        Route::get('/specs/list','SpecsController@list');
        Route::post('/specs/add','SpecsController@add');
        Route::post('/specs/update','SpecsController@update');
        Route::post('/specs/delete','SpecsController@delete');
        // 规格属性管理
        Route::get('/specsValue/search','SpecsValueController@search');
        Route::get('/specsValue/list','SpecsValueController@list');
        Route::post('/specsValue/add','SpecsValueController@add');
        Route::post('/specsValue/update','SpecsValueController@update');
        Route::post('/specsValue/delete','SpecsValueController@delete');
        // 商品管理
        Route::get('/goods/detail','GoodsController@detail');
        Route::get('/goods/list','GoodsController@list');
        Route::post('/goods/add','GoodsController@add');
        Route::post('/goods/update','GoodsController@update');
        Route::post('/goods/delete','GoodsController@delete');
        // 商品sku管理
        Route::post('/goodsSku/update','GoodsSkuController@update');
    });
});
