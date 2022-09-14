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
use Illuminate\Support\Facades\URL;
use Mail; 

class AuthenticationController extends Controller
{
  public function userregister(Request $request)
  {
    $data = $request->all();
    
     $validate = $this->validateRegisterationRequest($request->all());
        if($validate->fails()) return response()->json([
            'success'   => false,
            'error'     => $validate->errors(),
            'message'   => 'Invalid input, please check the errors.'
        ], 422);

    $request['role'] = "2";
    
    if ($request->hasFile('profile_photo')) {
      // Save image to folder
      $loc = '/public/user_profile_photos';
      $fileData = $request->file('profile_photo');
      $fileNameToStore = $this->uploadImage($fileData, $loc);
    } else {
      $fileNameToStore = 'no_img.jpg';
    }

    $user = new User;
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->phone_number = $request->phone_number;
    $user->profile_photo = $fileNameToStore;
    $user->location_id = $request->location_id;
    $user->status = "0";
    $user->save();

    // Save data into db

    // Attach role to user
    $role = Role::where('id', $request['role'])->first();
    $user->attachRole($role);
    
    
    
      $url=URL::to('https://backend.hostingladz.com/kids/admin/managers');

      $hello=Mail::send('email.userregistered', ['url' => $url, 'name' => $request->name,'email' => $request->email], function($message) use($request){
        $message->to("kids@yopmail.com");
        $message->subject('New location Manager Registered');
      });

    
    if (Mail::failures()) {
        return response(['statusCode' => '404', 'message' => 'User did not register'], 404);
    } else {
        return response(["Data" => $user, 'statusCode' => '200', 'message' => 'User Registration Request has been sent to admin for approval'], 201);

    }
    
  }
  
   protected function validateRegisterationRequest($data) {
        $validate = Validator::make($data, [
            'name'    => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required',
        ]);

        return $validate;
    }
    
    public function uploadImage($fileData, $loc)
    {
      // Get file name with extension
      $fileNameWithExt = $fileData->getClientOriginalName();
      // Get just file name
      $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
      // Get just extension
      $fileExtension = $fileData->extension();
      // File name to store
      $fileNameToStore = time() . '.' . $fileExtension;
      // Finally Upload Image
      $fileData->storeAs($loc, $fileNameToStore);

      return $fileNameToStore;
  }

  /**
   * Login api
   *
   * @return \Illuminate\Http\Response
   */
  public function userlogin(Request $request)
  {
    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
      // Get authenticated user
      $user = Auth::user();
      // create token
      $token =  $user->createToken('MyApp')->accessToken;

      return response()->json([
        'token'   => $token,
        'level'   => $user->level(),
        'verfication_status'   => $user->status,
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
