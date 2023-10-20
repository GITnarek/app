<?php

use App\Http\Controllers\OAuthController;
use App\Http\Middleware\VerifyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OmsController;

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

Route::middleware([VerifyRequest::class])->group(function () {
    Route::post('/store', [OmsController::class, 'store'])->name('oms.store');
    Route::post('/uninstall', [OAuthController::class, 'uninstall'])->name('oauth.uninstall');
});
