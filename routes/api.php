<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\FreightCalculatorController;
use App\Models\Branch;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get(
    '/v1/tracking/{tracking_number}',
    [TrackingController::class, 'show']
)->middleware('throttle:60,1');

Route::post('/v1/freight/quote', FreightCalculatorController::class)
    ->middleware('throttle:60,1');

    Route::get('/v1/branches', function () {
    return response()->json([
        'data' => Branch::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get(),
    ]);
})->middleware('throttle:60,1');
