<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BController;
use App\Http\Controllers\UserController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/bc',[BController::class,'index']);
Route::post('/bc',[BController::class,'store']);
Route::get('/bc/{id}',[BController::class,'show']);
Route::put('/bc/{id}',[BController::class,'update']);
Route::delete('/bc/{id}',[BController::class,'destroy']);


Route::prefix('auth')->group(function(){
    Route::post('/login',[UserController::class,'login']);
    Route::post('/register',[UserController::class,'register']);
    Route::middleware('auth:sanctum')->post('/logout',[UserController::class,'logout']);
});

?>
