<?php

use Illuminate\Http\Request;
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

Route::group(['prefix' => 'trips', 'namespace' => 'App\Http\Controllers\API\V1'], function () {
    Route::post('/store', 'TripsController@store');
    Route::get('/get', 'TripsController@get');
});
