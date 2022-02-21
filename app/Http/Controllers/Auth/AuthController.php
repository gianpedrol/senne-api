<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use DB;

use App\Models\User;


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
        $array['message'] = 'Incorrect username or password';
      }
      
      return $array;  
    }

    public function create(Request $request){

        $array = ['error' => ''];

        $data = $request->only(['name','cpfcnpj','email','nivel']);

        $user = User::where('cpfcnpj', $data['cpfcnpj'])->orWhere('email', $data['email'])->first();

        if(!empty($user)){
          return response()->json(['error'=>"User already exists!"],200);
        }

        //Define
        $role_id = $data['nivel'];
        $senha_temp= bcrypt(md5('123456'));

        $newUser = new User();
        $newUser->name = $data['name'];
        $newUser->email = $data['email'];
        $newUser->cpfcnpj = $data['cpfcnpj'];
        $newUser->role_id = $role_id;
        $newUser->password = $senha_temp;

        $newUser->save();

        return response()->json(['error'=>"User registered successfully!"],200);

    }
}
