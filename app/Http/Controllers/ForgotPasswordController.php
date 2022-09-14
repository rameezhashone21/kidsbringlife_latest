<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon; 
use App\Models\User; 
use Mail; 
use Hash;
use Illuminate\Support\Str;
use DB;

class ForgotPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitForgetPasswordForm(Request $request)
    {
         
      $data = $request->all();

      $validator = Validator::make($data, [
         'email' => 'required|email|exists:users',
       ]);

       $email_check=User::where('email',$request->email)->value('id');
       
       if (!isset($email_check)) {
        return response()->json([
          'message' => 'Email Not Found',
          'status'  => 0
        ], 200);
       }

      $token = Str::random(64);

      DB::table('password_resets')->insert([
        'email' => $request->email, 
        'token' => $token, 
        'created_at' => Carbon::now()
      ]);


      $url=URL::to('https://backend.hostingladz.com/kids/reset-password?token='.$token);

      $hello=Mail::send('email.forgetPassword', ['url' => $url], function($message) use($request){
        $message->to($request->email);
        $message->subject('Reset Password');
      });

      if (Mail::failures()) {
          return response()->json([
            'message' => 'Email Failed to send',
            'status'  => 0
          ], 200);
      }
      else{
          return response()->json([
            'message' => 'Email has been sent to email the users email address',
            'status'  => 1
          ], 200);
      }
          
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
      public function submitResetPasswordForm(Request $request, $token)
      {
          $data = $request->all();
          
          $validator = Validator::make($data, [
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
          ]);
  
          $updatePassword = DB::table('password_resets')
                              ->where([
                                'email' => $request->email, 
                                'token' => $token
                              ])
                              ->first();
  
          if(!$updatePassword){
            return response()->json([
              'message' => 'Invalid Token',
              'status'  => 0
            ], 200);
          }
  
          $user = User::where('email', $request->email)
                      ->update(['password' => Hash::make($request->password)]);
 
          DB::table('password_resets')->where(['email'=> $request->email])->delete();
  
          return response()->json([
            'message' => 'Password Reset Successfully Done',
            'status'  => 1
          ], 200);

      }

}
