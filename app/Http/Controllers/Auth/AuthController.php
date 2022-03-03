<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Str;

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

        /*
            Método responsável por gravar os usuários da Senne, esses uauários possui permissão role_id 1
            ele tem acesso a todo o conteudo do sistema
        */

        $data = $request->only(['name','cpf','email','cnpj','telefone']);

        $user = User::where('email', $data['email'])->first();

        if(!empty($user)){
            return response()->json(['error'=>"User already exists!"],200);
        }

        //Define nivel user Senne
        $role_id = 1;

        //$senha_md5= Str::random(8);//Descomentar após testes
        $senha_md5= '654321';
        $senha_temp= bcrypt($senha_md5);

        $newUser = new User();
        $newUser->name = $data['name'];
        $newUser->email = $data['email'];
        $newUser->cpf = $data['cpf'];
        $newUser->cnpj = $data['cnpj'];
        $newUser->telefone = $data['telefone'];
        $newUser->role_id = $role_id;
        $newUser->password = $senha_temp;

        $newUser->save();

        return response()->json(['message'=>"User registered successfully!", 'data'=>$newUser],200);

    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    { 
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    } 


    public function unauthorized()
    {
        return response()->json(['error'=>"Unauthorized user!"],401);
    }  
}
