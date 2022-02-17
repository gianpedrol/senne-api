<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(Request $request)
    {
      $array = ['message' => ''];

      $creds = $request->only('email','password');
      $token = Auth::attempt($creds);

      if($token){
        $user['email'] = $creds;  
        $array['token'] = $token;
      }else{ 
        $array['message'] = 'Login Errado';
      }
      
      return $array;  
    }

    public function create (Request $request){
          $array = ['error' => ''];

          $rules = [
              'email' =>'required|email|unique:users,email',
              'password' => 'required'
          ];

          $validator = Validator::make($request->all(),$rules);

          if($validator->fails()){
              $array['error'] = $validator->errors();

              return $array;
          }

          $email = $request->input('email');
          $password = $request->input('password');


          $newUser = new User();
          $newUser->email = $email;
          $newUser->password = password_hash($password, PASSWORD_DEFAULT);
          $newUser->token = '';
          $newUser->save();


          return $array;
    }
}
