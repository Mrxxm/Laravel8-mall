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
    });
});
