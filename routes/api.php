<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PraController;
use App\Http\Controllers\ParticipantAttendanceMealController;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//   return $request->user();
// });

// Public routes
Route::post('login', [AuthenticationController::class, 'userlogin']);
Route::post('register', [AuthenticationController::class, 'userregister']);
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm']); 
Route::post('reset-password/{token}', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.get');

Route::get('locations', [LocationController::class, 'get_all_locations'])->name('locations');

// Protected routes
Route::middleware(['auth:api'])->group(function () {

  // Admin Routes
  Route::prefix('/admin')->middleware(['checkrole:admin'])->group(function () {
    Route::prefix('/v1')->group(function () {
    
      // Update My profile
      Route::post('update-profile', [AuthenticationController::class, 'update_profile'])->name('update.profile');

      // Locations Routes
      Route::get('/locations', [LocationController::class, 'index'])->name('admin.locations');
      Route::post('/location/save', [LocationController::class, 'store'])->name('admin.location.save');
      Route::delete('/location/delete/{id}', [LocationController::class, 'destroy'])->name('admin.location.delete');
      Route::put('/location/update/{id}', [LocationController::class, 'update'])->name('admin.locaiton.update');
      Route::get('/location/get/{id}', [LocationController::class, 'details'])->name('admin.location.get');
      Route::get('/location/search/{string}', [LocationController::class, 'search'])->name('admin.location.search');
      
      Route::get('/location_without_pagination', [LocationController::class, 'get_all_locations'])->name('admin.locations.get_without_pagination');

      // Event Routes
      Route::get('/events', [EventController::class, 'index'])->name('admin.events');
      Route::get('/events_without_pagination', [EventController::class, 'get_all_events'])->name('admin.events.get_without_pagination');
      Route::post('/event/save', [EventController::class, 'store'])->name('admin.event.save');
      Route::delete('/event/delete/{id}', [EventController::class, 'destroy'])->name('admin.event.delete');
      Route::put('/event/update/{id}', [EventController::class, 'update'])->name('admin.event.update');
      Route::get('/event/get/{id}', [EventController::class, 'details'])->name('admin.event.get');
      Route::get('/event/report/{id}', [EventController::class, 'specific_event_report'])->name('admin.event.report');
      Route::get('/closeevent/{id}', [EventController::class, 'event_close'])->name('admin.event.close');

      Route::get('/event/participants/{id}', [EventController::class, 'admin_event_participants'])->name('admin.event.participants');
      Route::get('/event/participants/without_pagination/{id}', [EventController::class, 'admin_event_participants_without_pagination'])->name('admin.event.participants.without_pagination');
      Route::get('/event/users', [EventController::class, 'get_users'])->name('admin.event.users');
      Route::get('/event/assigned-users', [EventController::class, 'get_assigned_users'])->name('admin.event.assigned_users');
      Route::get('/event/search/{string}', [EventController::class, 'search'])->name('admin.event.search');

      // Meal Route
      Route::get('/get-meals', [EventController::class, 'get_meal'])->name('admin.meals');

      // User Routes
      Route::get('/users', [UserController::class, 'index'])->name('admin.users');
      Route::post('/user/save', [UserController::class, 'store'])->name('admin.user.save');
      Route::delete('/user/delete/{id}', [UserController::class, 'destroy'])->name('admin.user.delete');
      Route::post('/user/update/{id}', [UserController::class, 'update'])->name('admin.user.update');
      Route::get('/user/get/{id}', [UserController::class, 'details'])->name('admin.user.get');
      Route::get('/user/search/{string}', [UserController::class, 'search'])->name('admin.user.search');

      Route::post('/signature', [UserController::class, 'update_signature'])->name('update.signature');
      Route::get('/profile', [UserController::class, 'profile'])->name('update.signature');
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
      Route::get('/participant/search/{string}', [ParticipantController::class, 'search'])->name('user.participant.search');
      
      Route::get('/my-event', [EventController::class, 'my_event'])->name('user.event.get');

      Route::get('/my-event-participants', [EventController::class, 'event_participants'])->name('user.event.participants');
      
      Route::get('/event_participants_withoutpaginated', [EventController::class, 'event_participants_withoutpaginated'])->name('user.event.participants.withoutpaginated');
      
      Route::get('/my-meal-report', [EventController::class, 'meal_report'])->name('user.event.meal_report');

      Route::post('/mark-participant-attendance/{id}', [ParticipantAttendanceMealController::class, 'mark_attendance'])->name('user.participants.mark_attendance');
      Route::post('/mark-participant-meal/{id}', [ParticipantAttendanceMealController::class, 'mark_meal'])->name('user.participants.mark_meal');
      
      Route::get('/my-info', [UserController::class, 'my_info'])->name('user.my-info.get');

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
