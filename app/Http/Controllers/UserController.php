<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    // Get all data.
    $users = User::all();

    if (count($users) > 0) {
      return response(["Data" => $users, 'statusCode' => '200', 'message' => 'All Users'], 201);
    } else {
      return response(['statusCode' => '404', 'message' => 'No Data Found'], 404);
    }
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $data = $request->all();
    $validator = Validator::make($data, [
      'name' => 'required|max:255',
      'email' => 'required',
      'password' => 'required',
      'profile_photo' => 'required',
    ]);

    $request['role'] = "2";
    $request['status'] = "1";
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
    $user->profile_photo = $fileNameToStore;
    $user->status = "1";
    $user->save();

    // Save data into db

    // Attach role to user
    $role = Role::where('id', $request['role'])->first();
    $user->attachRole($role);

    if ($user) {
      return response(["Data" => $user, 'statusCode' => '200', 'message' => 'User Created Successfully'], 201);
    } else {
      return response(['statusCode' => '404', 'message' => 'User did not create'], 404);
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit(Role $role, $id)
  {
    // Get single user details
    $user = User::where('id', $id)->first();

    // Get All roles
    $roles = $role->all();

    return view('dashboard.admin.user.edit')
      ->with('user', $user)
      ->with('roles', $roles);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    // Validate data
    $data = $request->all();
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
      return response(["Data" => User::where('id', $id)->get(), 'statusCode' => '200', 'message' => 'User Updated Successfully'], 201);
    } else {
      return response(['statusCode' => '404', 'message' => 'User not updated'], 404);
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {

    // delete user profile image from database
    $user = User::where('id', $id)->first();

    if($user->roles[0]->level == 2) {
       return response()->json(['error'=>'Unauthorised', 'message' =>'Admin User cannot be deleted'], 419);
    }

    if($user){
    if ($user->profile_photo != 'no_img.jpg') {
      Storage::delete('public/user_profile_photos/' . $user->profile_photo);
    }
    if ($user) {
      //Delete user data
      $user = User::destroy($id);
      return response(["Data" => $user, 'statusCode' => '200', 'message' => 'User Deleted Successfully'], 201);
    } else {
      return response(['statusCode' => '404', 'message' => 'User not deleted'], 404);
    }
  }


  /**
   * Get the specific resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function details($id)
  {
    $user = User::where('id', $id)->get();
    if (count($user) > 0) {
      return response(["Data" => $user, 'statusCode' => '200', 'message' => 'User Details'], 201);
    } else {
      return response(['statusCode' => '404', 'message' => 'No Data Found'], 404);
    }
  }

  /**
   * Image upload.
   *
   * @param string $field
   * @param string $loc
   * @return \Illuminate\Http\Response
   */
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
   * Combine errors with messages
   *
   * @param object $message
   */
  public function sendError($message)
  {
    $message = $message->all();
    $response['error'] = "validation_error";
    $response['message'] = implode('', $message);
    $response['status'] = 0;
    return response()->json($response, 200);
  }
}
