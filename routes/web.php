<?php

use App\Http\Controllers\HeartbeatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/heartbeat', [HeartbeatController::class, 'index'])->name('heartbeat');
Route::get('/oauth/install', [OAuthController::class, 'install'])->name('oauth.install');
Route::get('/oauth/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
