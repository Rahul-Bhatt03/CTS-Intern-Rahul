<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LunchController;
use App\Http\Controllers\AttendanceController;


// apply cookie middleware to all api routes
// Route::middleware('cookieAuth')->group(function(){

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
// });

// Lunch Routes (All require authentication)
Route::prefix('lunch')->middleware('auth:sanctum')->group(function () {
    // Public settings (can be viewed by all authenticated users)
    Route::get('/settings', [LunchController::class, 'getSettings']);
    
    // User lunch request routes
    Route::post('/request', [LunchController::class, 'submitLunchRequest']);
    Route::get('/my-request', [LunchController::class, 'getUserLunchRequest']);
    Route::get('/my-history', [LunchController::class, 'getUserLunchHistory']);
    Route::delete('/request/{id}', [LunchController::class, 'deleteLunchRequest']);
    
    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::post('/settings', [LunchController::class, 'updateSettings']);
        Route::get('/requests', [LunchController::class, 'getRequests']);
        Route::put('/request/{id}/status', [LunchController::class, 'updateRequestStatus']);
    });
});

// Attendance Routes
Route::prefix('attendance')->middleware('auth:sanctum')->group(function () {
    Route::post('/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    Route::post('/submit', [AttendanceController::class, 'submitAttendance']);
    Route::get('/my-attendance', [AttendanceController::class, 'getUserAttendance']);
    
    // Admin only
    Route::middleware('admin')->group(function () {
        Route::get('/pending', [AttendanceController::class, 'getPendingAttendances']);
        Route::get('/submitted', [AttendanceController::class, 'getSubmittedAttendances']);
        Route::put('/{id}/status', [AttendanceController::class, 'updateAttendanceStatus']);
        Route::put('/bulk-status', [AttendanceController::class, 'bulkUpdateStatus']);
    });
});

?>
