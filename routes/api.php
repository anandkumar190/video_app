<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\IceConfigController;
use App\Http\Controllers\JoinController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('rooms', [RoomController::class, 'store']);
    Route::post('rooms/{room}/invites', [RoomController::class, 'createInvites']);
    Route::get('rooms/{room}/token', [RoomController::class, 'getToken']);
    Route::post('rooms/{room}/start', [RoomController::class, 'start']);
    Route::post('rooms/{room}/stop', [RoomController::class, 'stop']);
    Route::post('rooms/{room}/switch', [RoomController::class, 'switch']);
    Route::post('rooms/{room}/schedule', [RoomController::class, 'schedule']);

    Route::post('uploads', [UploadController::class, 'store']);
    Route::get('uploads', [UploadController::class, 'index']);

    Route::post('schedules/{schedule}/cancel', [ScheduleController::class, 'cancel']);

    Route::get('rtc/ice-config', [IceConfigController::class, 'getIceConfig']);
    Route::get('rtc/ice-stats', [IceConfigController::class, 'getIceStats']);
});

Route::post('join/{token}', [JoinController::class, 'guestJoin']);
