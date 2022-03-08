<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Hospitais;
use App\Models\UsersHospitals;
use App\Models\UserPermissoes;

class UserGroupController extends Controller
{

    public function __construct()
    {
            $this->middleware('auth:api');

            if( !auth()->user() ){
                return response()->json(['error'=>'Unauthorized access'],401);
            }

            /* 1 = Administrador Senne | 2 = User Labor | 3 = User Hospital | 4 = Médico | 5 = Paciente */
            if(auth()->user()->role_id != 1) {
                return response()->json(['error'=>'Unauthorized access'],401);
            }
        
    }


    public function createUserGroup(Request $request){

        $data = $request->only(['name','cpf','email','hospital','unidade', 'permissao']);
        $user = User::where('email', $data['email'])->first();

        if(!empty($user)){
            return response()->json(['error'=>"User already exists!"],200);
        }

        //Define nivel user Senne
        $role_id = 2;

        //$senha_md5= Str::random(8);//Descomentar após testes
        $senha_md5= '654321';
        $senha_temp= bcrypt($senha_md5);

        $newUser = new User();
        $newUser->name = $data['name'];
        $newUser->email = $data['email'];
        $newUser->cpf = $data['cpf'];
        $newUser->telefone = $data['telefone'];
        $newUser->role_id = $role_id;
        $newUser->password = $senha_temp;
        $newUser->save();
        
        $userHospital = new UsersHospitals();
        $userHospital->id_user = $newUser->id;
        $userHospital->id_hospital = $data['id_hospital'];
        $userHospital->save();

        $userPermissao = new UserPermissoes();
        $userPermissao->id_user = $newUser->id;
        $userPermissao->id_hospital =  $data['id_hospital'];
        $userPermissao->id_permissao = $data['permissao'];
        $userPermissao->save();


        return response()->json(['message'=>"User registered successfully!", 'data'=>$newUser],200);
        
    }

    public function listUserGroup(){
        
        $users = User::all();

        if($users)
        {
            foreach($users as $user){

                $data[] = [
                    'name' => $user->name,
                    'cpf' => $user->cpf,
                    'email' => $user->email,
                    'telefone' => $user->telefone,                    
                ];
            }

            return response()->json(
                ['status' => 'success', 'data' => $data], 
            200);

        }else{
            return response()->json(
                ['status' => 'labor not found'], 
            400);
        }    

    }

    public function showUserGroup($id){

        $user= User::where('id', $id)->first();

        return $user;
    }

    public function updateUserGroup(Request $request){

        $data = $request->only(['name','cpf','email','hospital','unidade', 'permissao']);
        $user = User::where('email', $data['email'])->first();

        if(!empty($user)){
            return response()->json(['error'=>"User already exists!"],200);
        }

        $validator = Validator::make($request->all());

        if($validator->fails()){
            $array['error'] = $validator->errors();
            return $array;
        }

        //Define nivel user Senne
        $role_id = 2;



        $newUser = new User();
        $newUser->name = $data['name'];
        $newUser->email = $data['email'];
        $newUser->cpf = $data['cpf'];
        $newUser->telefone = $data['telefone'];
        $newUser->role_id = $role_id;
        $newUser->save();
        
         $userPermissao = new UserPermissoes();
        $userPermissao->id_user = $newUser->id;
        $userPermissao->id_hospital =  $data['id_hospital'];
        $userPermissao->id_permissao = $data['permissao'];
        $userPermissao->save();


    }
}
