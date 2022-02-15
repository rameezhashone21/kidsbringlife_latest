<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
  public function adminregister(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required',
      'email' => 'required|email',
      'password' => 'required',
      'c_password' => 'required|same:password',
    ]);

    if ($validator->fails()) {
      return response()->json(['Validation Error.', $validator->errors()], 422);
    }

    $input = $request->all();
    $input['password'] = bcrypt($input['password']);
    $user = User::create($input);
    $success['token'] =  $user->createToken('MyApp')->accessToken;
    $success['name'] =  $user->name;

    return response()->json([$success, 'User register successfully.'], 201);
  }

  /**
   * Login api
   *
   * @return \Illuminate\Http\Response
   */
  public function adminlogin(Request $request)
  {
    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
      // Get authenticated user
      $user = Auth::user();
      // create token
      $token =  $user->createToken('MyApp')->accessToken;

      return response()->json([
        'token'   => $token,
        'level'   => $user->level(),
        'message' => 'Logged In Successfully',
        'status'  => 1
      ], 200);
    } else {
      return response()->json([
        'message' => 'Incorrect login details',
        'status'  => 0
      ], 200);
    }
  }

  public function update_profile(Request $request)
  {
    $data = $request->all();

    $id = Auth::id(); 

    $validator = Validator::make($data, [
      'name' => 'required|max:255',
      'email' => 'required',
      'password' => 'required',
      'profile_photo' => 'required',
    ]);

    if ($request->hasFile('profile_photo')) {
      // Save image to folder
      $loc = '/public/user_profile_photos';
      $fileData = $request->file('profile_photo');
      $fileNameToStore = $this->uploadImage($fileData, $loc);
      $data1 = [
        'profile_photo' => $fileNameToStore
      ];

      // Delete previous file
      $user = User::where('id', $id)->first();
      Storage::delete('public/user_profile_photos/' . $user->profile_photo);
    }

    // Check password was type on update
    if ($request->input('password')) {
      $data2 = [
        'password' => Hash::make($request->password)
      ];
    }

    // store data in array

    $data = [
      'name' => $request->name,
      'email' => $request->email,
      'status' => "1"
    ];

    // Merge all data arrays
    if ($request->hasFile('profile_photo') && $request->input('password')) {
      $data = array_merge($data1, $data2, $data);
    } else if ($request->hasFile('profile_photo') && !$request->input('password')) {
      $data = array_merge($data1, $data);
    } else if (!$request->hasFile('profile_photo') && $request->input('password')) {
      $data = array_merge($data2, $data);
    } else {
      $data = $data;
    }

    // Update data into db
    $user = User::where('id', $id)->update($data);

    if ($user) {
      return response(["result" => User::where('id', $id)->get(), 'status' => '1', 'message' => 'Profile Updated Successfully'], 201);
    } else {
      return response(['status' => '0', 'message' => 'Profile not updated'], 404);
    }
  }
}
