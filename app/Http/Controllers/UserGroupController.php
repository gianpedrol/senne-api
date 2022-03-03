<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Hospitais;
use App\Models\UsersHospitals;
use App\Models\UserPermissoes;

class UserGroupController extends Controller
{


    private function createUserGroup(Request $request){

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
}
