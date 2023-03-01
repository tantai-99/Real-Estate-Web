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
Route::group([
    'prefix' => 'v1api'
], function () {
    Route::get('/', IndexController::class . '@index');
    Route::get('search/pref', SearchController::class . '@pref');
    Route::get('search/city', SearchController::class . '@city');
    Route::post('search/city', SearchController::class . '@city');
    Route::get('search/eki', SearchController::class . '@eki');
    Route::post('search/eki', SearchController::class . '@eki');
    Route::get('search/choson', SearchController::class . '@choson');
    Route::get('search/line', SearchController::class . '@line');
    Route::post('search/line', SearchController::class . '@line');
    Route::get('search/result', SearchController::class . '@result');
    Route::post('search/result', SearchController::class . '@result');
    Route::get('search/detail', SearchController::class . '@detail');
    Route::get('search/spatial-city', SearchController::class . '@spatialCity');
    Route::post('search/spatial-city', SearchController::class . '@spatialCity');
    Route::post('search/condition', SearchController::class . '@condition');
    Route::get('search/howtoinfo', SearchController::class . '@howtoinfo');
    Route::get('search/spatial-map', SearchController::class . '@spatialMap');
    Route::get('search/shumoku', SearchController::class . '@shumoku');
    Route::get('search/rent', SearchController::class . '@rent');
    Route::get('search/purchase', SearchController::class . '@purchase');
    Route::get('search/spatial-mapcenter', SearchController::class . '@spatialMapcenter');
    Route::post('search/spatial-estate', SearchController::class . '@spatialEstate');
    Route::get('search/spatial-estatelist', SearchController::class . '@spatialEstatelist');
    Route::get('search/favorite', SearchController::class . '@favorite');
    Route::get('search/history', SearchController::class . '@history');
    
    Route::get('special/pref', SpecialController::class . '@pref');
    Route::get('special/city', SpecialController::class . '@city');
    Route::post('special/city', SpecialController::class . '@city');
    Route::get('special/line', SpecialController::class . '@line');
    Route::post('special/line', SpecialController::class . '@line');
    Route::get('special/eki', SpecialController::class . '@eki');
    Route::post('special/eki', SpecialController::class . '@eki');
    Route::get('special/spatial-city', SpecialController::class . '@spatialCity');
    Route::post('special/spatial-city', SpecialController::class . '@spatialCity');
    Route::get('special/result', SpecialController::class . '@result');
    Route::post('special/result', SpecialController::class . '@result');
    Route::get('special/choson', SpecialController::class . '@choson');
    Route::post('special/condition', SpecialController::class . '@condition');
    Route::get('special/spatial-map', SpecialController::class . '@spatialMap');    
    Route::get('special/spatial-mapcenter', SpecialController::class . '@spatialMapcenter');
    Route::post('special/spatial-estate', SpecialController::class . '@spatialEstate');
    Route::get('special/spatial-estatelist', SpecialController::class . '@spatialEstatelist');
    Route::get('special/counter', SpecialController::class . '@counter');
    
    Route::post('house/house-all', HouseController::class . '@houseAll');
    Route::get('house/house-all', HouseController::class . '@houseAll');

    Route::get('inquiry/edit', InquiryController::class . '@edit');
    Route::get('inquiry/confirm', InquiryController::class . '@confirm');
    Route::get('inquiry/complete', InquiryController::class . '@complete');
    Route::get('inquiry/error', InquiryController::class . '@error');

    Route::get('parts/koma', PartsController::class . '@koma');
    Route::post('parts/count-bukken', PartsController::class . '@countBukken');
    Route::post('parts/suggest', PartsController::class . '@suggest');
    Route::post('parts/estatelist', PartsController::class . '@estatelist');
    Route::get('parts/estatelist', PartsController::class . '@estatelist');
    Route::post('parts/modal', PartsController::class . '@modal');
    Route::post('parts/count', PartsController::class . '@count');
    Route::get('parts/koma-top', PartsController::class . '@komaTop');
    Route::post('parts/koma-top', PartsController::class . '@komaTop');
    Route::post('parts/saveOperation', PartsController::class . '@saveOperation');
});
