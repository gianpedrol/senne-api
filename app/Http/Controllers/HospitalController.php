<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Hospitais;
use App\Models\UsersHospitals;

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

        $hospitals = Hospitais::all();

        if($hospitals)
        {
            foreach($hospitals as $hospital){

                $data[] = [
                    'name' => $hospital->name,
                ];
            }

            return response()->json(
                ['status' => 'success', 'data' => $data], 
            200);
        }else{
            return response()->json(
                ['status' => 'Hospital not found'], 
            400);
        }        
    }

    //salva usuario hospital
    public function storeUserHospital(Request $request){

        //Definimos ID do user como 2 (Usuário Hospital)
        $role_id= 2;

        $data = $request->only(['name','telefone','cpf']);

        $user = User::where('cpf', $data['cpf'])->first();

        if(!empty($user)){
          return response()->json(['error'=>"User already exists!"],200);
        }

        //Define
        $senha_temp= bcrypt(md5('123456'));

        $newUser = new User();
        $newUser->name = $data['name'];
        $newUser->cpf = $data['cpf'];
        $newUser->role_id = $role_id;
        $newUser->password = $senha_temp;

        $newUser->save();

        return response()->json(['message'=>"User registered successfully!"],200);

    }

}
