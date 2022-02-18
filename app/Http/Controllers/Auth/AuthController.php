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

    public function create (Request $request){

          $array = ['error' => ''];

          $rules = [
            'name' => 'required',
            'email' =>'required|email|unique:users,email',
          ];
      
          $validator = Validator::make($request->all(),$rules);
          $user = User::where('name')->get();
         dd($user);

          

          if($validator->fails()){
              $array['error'] = $validator->errors();    
              
              
              
              return $array;
          } else if($user) {


            $message = 'name exists';

            return $message;

          }else{            
            $name = $request->input('name');
            $email = $request->input('email');
            $role_id = 1;
            $senha_temp= bcrypt(md5('123456'));


            $newUser = new User();
            $newUser->name = $name;
            $newUser->email = $email;
            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            
            $newUser->save();
   
            $message = ['message' => 'Parabéns você conseguiu, não desista amanhã é sexta feira!'];
            
            return $message;
          }



    }
}
