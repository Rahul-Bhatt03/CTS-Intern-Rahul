<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\resources\views;

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

Route::get('/signin', function () {
    return view('Auth.signin');
});

Route::get('/', function () {
    return view('Employee.home');
});

// Route::get('/student', function () {
//     return view('student');
// });

// Route::get('/book',function(){
//     return view('book');
// });

Route::resource('/book',BookController::class);
