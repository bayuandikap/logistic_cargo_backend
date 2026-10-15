<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrackingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get(
    '/v1/tracking/{tracking_number}',
    [TrackingController::class, 'show']
)->middleware('throttle:60,1');
