<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
  return $request->user();
});

  
Route::post('login', [AuthenticationController::class, 'login']);
Route::post('register', [AuthenticationController::class, 'register']);
     
Route::middleware('auth:api','user_check')->group( function () {
   
   //Locations Routes 
   Route::get('get-all-location', [LocationController::class, 'index']);
   Route::post('save-location', [LocationController::class, 'store']);
   Route::delete('delete-location/{id}',[LocationController::class,'destroy']);
   Route::put('update-location/{id}',[LocationController::class,'update']);
   Route::get('get-specific-location/{id}',[LocationController::class,'details']);

   //Event Routes
   Route::get('get-all-events', [EventController::class, 'index']);
   Route::post('save-event', [EventController::class, 'store']);
   Route::delete('delete-event/{id}',[EventController::class,'destroy']);
   Route::put('update-event/{id}',[EventController::class,'update']);
   Route::get('get-specific-event/{id}',[EventController::class,'details']);
   Route::get('get-users', [EventController::class, 'get_users']);

   //User Routes
   Route::get('get-all-users', [UserController::class, 'index']);
   Route::post('save-user', [UserController::class, 'store']);
   Route::delete('delete-user/{id}',[UserController::class,'destroy']);
   Route::put('update-user/{id}',[UserController::class,'update']);
   Route::get('get-specific-user/{id}',[UserController::class,'details']);
});
