<?php

use Illuminate\Http\Request;

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

Route::post('login', 'AuthApiController@login');
Route::post('register', 'AuthApiController@register');

Route::group(['middleware' => 'auth.jwt'], function () {
    Route::get('logout', 'AuthApiController@logout');

    Route::get('user', 'AuthApiController@getAuthUser');

    Route::resource('products', 'ProductApiController');
});
