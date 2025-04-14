<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardDailyApiController;
use App\Http\Controllers\Api\CoalPriceController;
use App\Http\Controllers\SummaryController;

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

Route::get('/daily/reguler', [DashboardDailyApiController::class, 'reguler_daily']);
Route::get('/daily/capex', [DashboardDailyApiController::class, 'capex_daily']);
Route::get('/unit-summary', [SummaryController::class, 'getUnitSummary']);
Route::get('/coal-prices', [CoalPriceController::class, 'getCoalPrices']);
