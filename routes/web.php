<?php

use Illuminate\Support\Facades\Route;

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

Route::get("/", function () {
    abort(404);
});

// Temporary login route to prevent "Route [login] not defined" error
Route::get('/login', function () {
    return response()->json([
        'success' => false,
        'message' => 'Please use API login: POST /api/v1/login',
        'data'    => null,
    ], 401);
})->name('login');
