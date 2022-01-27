<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
  public function register(Request $request)
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
  public function login(Request $request)
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
}
