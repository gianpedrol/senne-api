<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function login(Request $request)
    {
      $array = ['message' => ''];

      $creds = $request->only('email','password');
      $token = Auth::attempt($creds);

      if($token){
        $array['token'] = $token;
      }else{
        $array['message'] = 'Login Errado';
      }
      
      return $array;
     


    }
}
