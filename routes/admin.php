<?php

Route::group(['namespace' => 'App\Http\Controllers\Admin'], function () {

    Route::get('/login/getAppToken','LoginController@getAppToken');

});
