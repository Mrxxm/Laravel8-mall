<?php

Route::group(['namespace' => 'App\Http\Controllers\Admin'], function () {

    Route::get('/login/getAppToken','LoginController@getAppToken');

    Route::group(['middleware' => ['checkAdminToken']], function () {

        // 后台用户管理
        Route::get('/adminUser/list','AdminUserController@list');
        Route::get('/adminUser/add','AdminUserController@add');
        Route::get('/adminUser/update','AdminUserController@update');
        Route::get('/adminUser/delete','AdminUserController@delete');

        // 前台用户管理
        Route::get('/user/list','UserController@list');
        Route::get('/user/update','UserController@update');
        Route::get('/user/delete','UserController@delete');


    });
});
