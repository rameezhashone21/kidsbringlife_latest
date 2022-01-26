<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AuthenticationController;


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

// Public routes
Route::post('login', [AuthenticationController::class, 'login']);
Route::post('register', [AuthenticationController::class, 'register']);

// Protected routes
Route::middleware(['auth:api'])->group(function () {

  //Admin Routes
  Route::prefix('/admin')->middleware(['checkrole:admin'])->group(function () {
    Route::prefix('/v1')->group(function () {
      //Locations Routes
      Route::get('/locations', [LocationController::class, 'index'])->name('admin.locations');
      Route::post('/location/save', [LocationController::class, 'store'])->name('admin.location.save');
      Route::delete('/location/delete/{id}', [LocationController::class, 'destroy'])->name('admin.location.delete');
      Route::put('/location/update/{id}', [LocationController::class, 'update'])->name('admin.locaiton.update');
      Route::get('/location/get/{id}', [LocationController::class, 'details'])->name('admin.location.get');

      //Event Routes
      Route::get('/events', [EventController::class, 'index'])->name('admin.events');
      Route::post('/event/save', [EventController::class, 'store'])->name('admin.event.save');
      Route::delete('/event/delete/{id}', [EventController::class, 'destroy'])->name('admin.event.delete');
      Route::put('/event/update/{id}', [EventController::class, 'update'])->name('admin.event.update');
      Route::get('/event/get/{id}', [EventController::class, 'details'])->name('admin.event.get');
      Route::get('/event/users', [EventController::class, 'get_users'])->name('admin.event.users');

      //User Routes
      Route::get('/users', [UserController::class, 'index'])->name('admin.users');
      Route::post('/user/save', [UserController::class, 'store'])->name('admin.user.save');
      Route::delete('/user/delete/{id}', [UserController::class, 'destroy'])->name('admin.user.delete');
      Route::put('/user/update/{id}', [UserController::class, 'update'])->name('admin.user.update');
      Route::get('/user/get/{id}', [UserController::class, 'details'])->name('admin.user.get');
    });
  });
});
