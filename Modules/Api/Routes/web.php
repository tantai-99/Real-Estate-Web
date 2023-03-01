<?php

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

Route::prefix('api')->group(function() {
    Route::get('/', 'ApiController@index');
    Route::get('kk-api/get-auth-session', 'KkApiController@getAuthSession');

    Route::post('estate-contact/validate', 'EstateContactController@validateForm');
    Route::post('estate-contact/send-inquiry', 'EstateContactController@sendInquiry');

    Route::post('contact/validate', 'ContactController@validateForm');
    Route::post('contact/send-inquiry', 'ContactController@sendInquiry');

    Route::post('estate-request/validate', 'EstateRequestController@validateForm');
    Route::post('estate-request/send-inquiry', 'EstateRequestController@sendInquiry');

    Route::post('commission/tracking', 'CommissionController@tracking');

    Route::get('conversion/tel-tap', 'ConversionController@telTap');

    Route::get('stb/get-kokyaku-kanri-keiyaku-kaiin-no/{member_no}', 'StbController@getKokyakuKanriKeiyakuKaiinNo');
});
