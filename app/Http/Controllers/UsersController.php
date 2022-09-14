<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    // Get all data.
    $user = User::all();

    return view('dashboard.admin.user.index')->with('users', $user);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    // Get All roles
    $roles = Role::all();

    return view('dashboard.admin.user.add')->with('roles', $roles);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    // Validate data
    $valid = $this->validate($request, [
      'name' => 'required|string',
      'email' => 'required|string|unique:users,email',
      'password' => 'required|string',
      'profile_photo' => 'required|image|max:2048',
      'role' => 'required',
      'status' => 'required',
    ]);

    if ($request->hasFile('profile_photo')) {
      // Save image to folder
      $loc = '/public/user_profile_photos';
      $fileData = $request->file('profile_photo');
      $fileNameToStore = $this->uploadImage($fileData, $loc);
    } else {
      $fileNameToStore = 'no_img.jpg';
    }

    $data = [
      'name' => $valid['name'],
      'email' => $valid['email'],
      'password' => Hash::make($valid['password']),
      'profile_photo' => $fileNameToStore,
      'status' => $valid['status']
    ];

    // Save data into db
    $user = User::create($data);;

    // Attach role to user
    $role = Role::where('id', $valid['role'])->first();
    $user->attachRole($role);

    if ($user) {
      return redirect('/admin/users')->with('success', 'Record created successfully.');
    } else {
      return redirect('/admin/users')->with('error', 'Record not created!');
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
    $valid = $this->validate($request, [
      'name' => 'required|string',
      'email' => 'required|string',
      'profile_photo' => 'image|max:2048',
      'status' => 'required',
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
      'name' => $valid['name'],
      'email' => $valid['email'],
      'status' => $valid['status']
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
      return redirect('/admin/users')->with('success', 'Record updated successfully.');
    } else {
      return redirect('/admin/users')->with('error', 'Record not updated!');
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

    // delete user profile image
    $user = User::where('id', $id)->first();
    if ($user->profile_photo != 'no_img.jpg') {
      Storage::delete('public/user_profile_photos/' . $user->profile_photo);
    }

    //Delete user data
    $user = User::destroy($id);

    if ($user) {
      return redirect('/admin/users')->with('success', 'Record Deleted Successfully.');
    } else {
      return redirect('/admin/users')->with('error', "Record not deleted!");
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
   * User registration
   *
   * @return Illuminate\Http\Request $request
   * @return Illuminate\Http\Response
   */
  public function register(Request $request)
  {
    // Validate data
    $validator = Validator::make($request->all(), [
      'name'         => 'required',
      'email'        => 'required|email|regex:/^[a-zA-Z]{1}/|unique:users,email',
      'password'     => ['required', 'confirmed', Rules\Password::defaults()],
      'phone_number' => 'required|numeric|digits_between:10,11|unique:users,phone_number',
      'device_name'  => 'required'
    ]);

    if ($validator->fails()) {
      return $this->sendError($validator->errors());
    }

    $data = [
      'name'          => $request->name,
      'email'         => $request->email,
      'password'      => Hash::make($request->password),
      'phone_number'  => $request->phone_number,
      'status'        => "1",
    ];

    // Insert record
    $user  = User::create($data);

    // Get user role //
    $userRole = Role::where('slug', '=', 'user')->first();
    // Attach role to user //
    $user->attachRole($userRole);

    // Create Token
    $token = $user->createToken($request->device_name)->plainTextToken;

    if ($user) {
      return response()->json([
        'result'  => $user,
        'token'   => $token,
        'message' => "Registered Successfully",
        'status'  => 1
      ]);
    } else {
      return response()->json([
        'message' => "Sorry something went wrong !",
        'status'  => 0
      ]);
    }
  }

  /**
   * User login
   *
   * @return Illuminate\Http\Request $request
   * @return Illuminate\Http\Response
   */
  public function login(Request $request)
  {
    // Validate data
    $validator = Validator::make($request->all(), [
      'email'        => 'required',
      'password'     => 'required',
      'device_name'  => 'required',
    ]);

    if ($validator->fails()) {
      return $this->sendError($validator->errors());
    }

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
      return response()->json([
        'message' => "Incorrect login details",
        'status'  => 0
      ]);
    }

    if ($user->status == 1) {
      // Create Token
      $token = $user->createToken($request->device_name)->plainTextToken;

      return response()->json([
        'result'  => $user,
        'token'   => $token,
        'message' => "Logged In Successfully",
        'status'  => 1
      ]);
    } else {
      return response()->json([
        'message' => "Your account has been blocked",
        'status'  => 0
      ]);
    }
  }

  /**
   * User Logout
   *
   * @return Illuminate\Http\Response
   */
  public function logout(Request $request)
  {
    auth()->user()->tokens()->delete();

    return response()->json([
      'message' => "Logout successfully",
      'status'  => 1
    ]);
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