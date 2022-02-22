<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ForgotPasswordController;


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


Route::post('login', [AuthenticationController::class, 'adminlogin']);
Route::post('register', [AuthenticationController::class, 'adminregister']);
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm']); 
Route::post('reset-password/{token}', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.get');




// Protected routes
Route::middleware(['auth:api'])->group(function () {

  //Admin Routes
  Route::prefix('/admin')->middleware(['checkrole:admin'])->group(function () {
    Route::prefix('/v1')->group(function () {
    
      //Update My profile
      Route::post('update-profile', [AuthenticationController::class, 'update_profile'])->name('update.profile');

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
      Route::get('/closeevent/{id}', [EventController::class, 'event_close'])->name('admin.event.close');
      Route::get('/event/users', [EventController::class, 'get_users'])->name('admin.event.users');

      //Meal Route
      Route::get('/get-meals', [EventController::class, 'get_meal'])->name('admin.meals');


      //User Routes
      Route::get('/users', [UserController::class, 'index'])->name('admin.users');
      Route::post('/user/save', [UserController::class, 'store'])->name('admin.user.save');
      Route::delete('/user/delete/{id}', [UserController::class, 'destroy'])->name('admin.user.delete');
      Route::put('/user/update/{id}', [UserController::class, 'update'])->name('admin.user.update');
      Route::get('/user/get/{id}', [UserController::class, 'details'])->name('admin.user.get');
    });
  });
});



// Protected routes
Route::middleware(['auth:api'])->group(function () {

  //Admin Routes
  Route::prefix('/user')->group(function () {
    Route::prefix('/v1')->group(function () {
      
      //Update My profile
      Route::post('update-profile', [AuthenticationController::class, 'update_profile'])->name('update.profile');

      //Locations Routes
      Route::get('/participants', [ParticipantController::class, 'index'])->name('user.participants');
      Route::post('/participant/save', [ParticipantController::class, 'store'])->name('user.participant.save');
      Route::delete('/participant/delete/{id}', [ParticipantController::class, 'destroy'])->name('user.participant.delete');
      Route::put('/participant/update/{id}', [ParticipantController::class, 'update'])->name('user.participant.update');
      Route::get('/participant/get/{id}', [ParticipantController::class, 'details'])->name('user.participant.get');

      Route::get('/my-event', [EventController::class, 'my_event'])->name('user.event.get');


      // //Event Routes
      // Route::get('/events', [EventController::class, 'index'])->name('admin.events');
      // Route::post('/event/save', [EventController::class, 'store'])->name('admin.event.save');
      // Route::delete('/event/delete/{id}', [EventController::class, 'destroy'])->name('admin.event.delete');
      // Route::put('/event/update/{id}', [EventController::class, 'update'])->name('admin.event.update');
      // Route::get('/event/get/{id}', [EventController::class, 'details'])->name('admin.event.get');
      // Route::get('/event/users', [EventController::class, 'get_users'])->name('admin.event.users');

      // //User Routes
      // Route::get('/users', [UserController::class, 'index'])->name('admin.users');
      // Route::post('/user/save', [UserController::class, 'store'])->name('admin.user.save');
      // Route::delete('/user/delete/{id}', [UserController::class, 'destroy'])->name('admin.user.delete');
      // Route::put('/user/update/{id}', [UserController::class, 'update'])->name('admin.user.update');
      // Route::get('/user/get/{id}', [UserController::class, 'details'])->name('admin.user.get');
    });
  });
});
