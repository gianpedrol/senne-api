<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Hospitais;
use App\Models\UsersHospitals;
use App\Models\UserPermissoes;

class HospitalController extends Controller
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

    //Salva Hospital
    public function storeHospital(Request $request){

        $data= $request->only('name', 'id_api');


        Hospitais::create(['name'=>$data['name'], 'id_api'=>$data['id_api']]);

        return response()->json(['message'=>'Hospital create successfully'],200);
    	
    }

    //lista Hospitais
    public function listHospitals(){

        /* 1 = Administrador Senne | 2 = User Labor | 3 = User Hospital | 4 = Médico | 5 = Paciente */
        if(auth()->user()->role_id != 1) {
            return response()->json(['error'=>'Unauthorized access!'],401);
        }   

        $hospitals = Hospitais::all();

        if( count($hospitals) > 0 )
        {
            foreach($hospitals as $hospital){

                $data[] = [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                ];
            }

            return response()->json(
                ['status' => 'success', 'data' => $data], 
            200);
        }else{
            return response()->json(
                ['status' => 'hospital is empty!'], 
            404);
        }        
    }


    public function getHospital($id)
    {
        $hospital= Hospitais::where('id', $id)->first();

        return $hospital;
    }

    //salva usuario hospital
    public function storeUserHospital(Request $request)
    {

        //Definimos ID do user como 2 (Usuário Hospital)
        $role_id= 2;

        //Definimos o tipo do usuario por padrão administrador
        $nivel_user= 1;

        $data = $request->only(['name','telefone','cpf','email','id_hospital']);

        if(empty($data['id_hospital'])){
            return response()->json(['error'=>'ID Hospital cannot be empty'],404);
        }else if( empty( $this->getHospital($data['id_hospital']) ) ){
            return response()->json(['error'=>'Hospital not found'],404);
        }

        $user = User::where('email', $data['email'])->first();

        if(!empty($user)){
          return response()->json(['error'=>"User already exists!"],200);
        }

        //Define
        $senha_temp= bcrypt(md5('123456'));

        $newUser = new User();
        $newUser->name = $data['name'];
        $newUser->cpf = $data['cpf'];
        $newUser->telefone = $data['telefone'];
        $newUser->email = $data['email'];
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
        $userPermissao->id_permissao = $nivel_user;
        $userPermissao->save();


        return response()->json(['message'=>"User registered successfully!"],200);

    }

}
