<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use DB;
use Validator;

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
   
        if($validator->fails()){
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
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user();
            if($user->roles[0]->level != 2) {
                return response()->json(['error'=>'Unauthorised', 'message' =>'You are not an Admin User'], 419);
            }
            $success['token'] =  $user->createToken('MyApp')-> accessToken; 
            $success['id'] =  $user->id;
            $success['name'] =  $user->name;
            $success['email'] =  $user->email;
            $success['photo'] =  $user->profile_photo;
   
            return response()->json([$success, 'User logged In successfully.'], 200);
        } 
        else{ 
            
            return response(["message" => 'Invalid Email or Password.'], 401);

        } 
    }
}
