<?php
use App\Http\Controllers\Api\CaseController;

Route::get('/cases', [CaseController::class, 'index']);
Route::get('/cases/{id}', [CaseController::class, 'show']);
