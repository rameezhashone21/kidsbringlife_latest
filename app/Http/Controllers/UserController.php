<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
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
  public function index(Request $request)
  {
    $users= User::with('roles')->with('locations')->orwhere('name','LIKE','%'.$request->q.'%')->whereHas('roles', function($q) {
        $q->whereNotIn('level', [2]);
    })->orderBy('id', 'DESC')->paginate(10);
    
    if (count($users) > 0) {
      return response()->json([
        "result"  => $users,
        "message" => "Success",
        "status"  => 1
      ], 200);
    } else {
      return response()->json([
        "result"  => array(),
        "message" => "Record not found.",
        "status"  => 0
      ], 200);
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
    $user->address = $request->address;
    $user->postal_code = $request->postal_code;
    $user->state = $request->state;
    $user->city = $request->city;
    $user->status = $request->status;
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
   
    protected function validateRegisterationRequest($data) {
        $validate = Validator::make($data, [
            'name'    => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required',
            'profile_photo'      => 'required',
            'address'      => 'required|string',
            'postal_code'      => 'required|string',
            'state'  => 'required|string',
            'city'  => 'required|string',
            'phone_number'        => 'required|integer'
        ]);

        return $validate;
    }
    
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
      //dd($request);
    // Validate data
    $data = $request->all();
    $validator = Validator::make($data, [
      'password'      => 'required',
      'profile_photo'      => 'required',
      'address'      => 'required|string',
      'postal_code'      => 'required|string',
      'state'  => 'required|string',
      'city'  => 'required|string',
      'phone_number'        => 'required|integer'
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
      'address' => $request->address,
      'postal_code' => $request->postal_code,
      'state' => $request->state,
      'city' => $request->city,
      'phone_number' => $request->phone_number,
      'status' => $request->status,
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

    if ($user->roles[0]->level == 2) {
      return response()->json(['error' => 'Unauthorised', 'message' => 'Admin User cannot be deleted'], 419);
    }

    if ($user) {
      if ($user->profile_photo != 'no_img.jpg') {
        Storage::delete('public/user_profile_photos/' . $user->profile_photo);
      }
      if ($user) {
        //Delete user data
        $user = User::destroy($id);
        return response(["Data" => $user, 'statusCode' => '1', 'message' => 'User Deleted Successfully'], 200);
      } else {
        return response(["Data" => array(), 'statusCode' => '0', 'message' => 'User not deleted'], 200);
      }
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
      return response(["Data" => $user, 'statusCode' => '1', 'message' => 'User Details'], 200);
    } else {
      return response(["Data" => array(), 'statusCode' => '0', 'message' => 'No Data Found'], 200);
    }
  }
  
  /**
   * Get the specific resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function my_info()
  {
    $user = auth()->user();
    if (isset($user)) {
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

  public function update_signature(Request $request) {

    $validate = Validator::make($request->all(), [
      'signature'   => 'required|image'
    ]);
    
    if($validate->fails()) {
      return response()->json([
        'errors'  => $validate->errors(),
        'message'  => 'unprocessable entity' 
      ], 422);
    }

    $user = User::where('id', auth()->user()->id)->first();
    if($user) {

      if ($request->hasFile('signature')) {
        // Save image to folder
        $loc = '/public/signature';
        $fileData = $request->file('signature');
        $signatureNameToStore = $this->uploadImage($fileData, $loc);
      } else {
        $signatureNameToStore = null;
      }

      if($user->signature != null) File::delete(storage_path('app/public/signature/'. $user->signature));

      $user->update([
        'signature' => $signatureNameToStore
      ]);

      $user->signature_url = Storage::url('signature/'. $user->signature);

      return response()->json([
        'error'   => false,
        'data'    => $user->toArray(),
        'message' => 'Signature Successfully Updated.',
        'status'  => 1,
      ], 200);
    } else {

      return response()->json([
        'error'   => true,
        'message' => 'Unprocessed entity.',
        'status'  => 0,
      ], 422);
    }
  }
}
