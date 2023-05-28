<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserPreferenceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//Authencation



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');


// Route::get('/articles', 'ArticleController@index');
Route::get('/articles', [ArticleController::class, 'index']);

// Route for Options
Route::get('/articles/options', [ArticleController::class, 'getOptions']);


//Route for user_prefrences
Route::post('/update-preferences', [ArticleController::class, 'updatePreferences']);

// Route to fetch user prefrences
Route::get('/user-preferences/{user_id}', [ArticleController::class, 'getUserPreferences']);