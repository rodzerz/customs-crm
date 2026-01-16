<?php
use App\Http\Controllers\Api\CaseController;

Route::get('/cases', [CaseController::class, 'index']);
Route::get('/cases/{id}', [CaseController::class, 'show']);


use App\Http\Controllers\Api\VehicleController;

Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);