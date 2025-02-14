<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\EvaluationTokenController;
use App\Http\Controllers\Api\LogController;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');

Route::get('/modules', [ModuleController::class, 'index']);
Route::get('/modules/{module}/groups', [ModuleController::class, 'getGroups']);
Route::get('/evaluation-tokens/recent', [EvaluationTokenController::class, 'recent']);
Route::post('/log', [LogController::class, 'store'])->name('api.log');
