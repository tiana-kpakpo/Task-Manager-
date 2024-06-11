<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/task', [TaskController::class, 'index']);
Route::get('/task/search', [TaskController::class, 'search']);
Route::get('/users', [AuthController::class, 'indexU']);

// protected routes
Route::middleware('auth:sanctum') -> group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/task', [TaskController::class, 'store']);
    Route::get('/task/{id}', [TaskController::class, 'show']);
    Route::patch('/task/{id}', [TaskController::class, 'update']);
    Route::delete('/task/{id}', [TaskController::class, 'destroy']);

});




// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
