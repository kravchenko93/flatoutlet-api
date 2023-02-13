<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\FavoriteFlatsController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\OfferDayController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::post('flats', [FlatController::class, 'getFlats']);
    Route::post('developers', [FlatController::class, 'getDevelopers']);
    Route::get('banner', [BannerController::class, 'getBanner']);
    Route::post('flat_favorites', [FavoriteFlatsController::class, 'getFavorites']);
    Route::post('delete_flat_from_favorites', [FavoriteFlatsController::class, 'deleteFromFavorites']);
    Route::post('add_flat_to_favorites', [FavoriteFlatsController::class, 'addToFavorites']);
    Route::get('offer_day', [OfferDayController::class, 'getOfferDay']);
    Route::post('callback', [CallbackController::class, 'callback']);
});
