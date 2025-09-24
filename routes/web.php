<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomViewController;
use App\Http\Controllers\JoinController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('join/{token}', [JoinController::class, 'join']);
Route::get('/rooms/{room}/viewer', [RoomViewController::class, 'viewer'])->name('room.viewer');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rooms/{room}/control', [RoomViewController::class, 'control'])->name('room.control');
});

require __DIR__.'/auth.php';
