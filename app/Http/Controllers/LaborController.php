<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\UsersLabors;
use App\Models\User;
use App\Models\Labors;
use Illuminate\Support\Facades\Http;

use GuzzleHttp\Client as GuzzleHttpClient;

class LaborController extends Controller
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


    //Salva Laboratório
    public function store(Request $request){

        $data = $request->only(['name','cnpj','telefone']);

        $labor = Labors::where('cnpj', $data['cnpj'])->first();

        if(!empty($labor)){
            return response()->json(['error'=>"Labor already exists!"],200);
        }

        $newLabor = new Labors();
        $newLabor->name = $data['name'];
        $newLabor->telefone = $data['telefone'];
        $newLabor->cnpj = $data['cnpj'];

        $newLabor->save();

        return response()->json(['message'=>"Labor registered successfully!"],200);

 
    }

    //Salva usuário Laboratório
    public function storeUser(Request $request){

        //Definimos ID do user como 2 (Usuário Hospital)
        $role_id= 2;

        $data = $request->only(['name','telefone','cpf','id_labor']);

        $user = User::where('cpf', $data['cpf'])->first();

        if(!empty($user)){
          return response()->json(['error'=>"User already exists!"],200);
        }

        //Define
        $senha_temp= bcrypt(md5('123456'));

        $newUserLabor = new User();
        $newUserLabor->name = $data['name'];
        $newUserLabor->cpf = $data['cpf'];
        $newUserLabor->role_id = $role_id;
        $newUserLabor->password = $senha_temp;
        $newUserLabor->save();
        

        $userLabor = new UsersLabors();
        $userLabor->id_user = $newUserLabor->id;
        $userLabor->id_labor = $data['id_labor'];
        $userLabor->save();


        return response()->json(['message'=>"User registered successfully!"],200);

    }

    //Lista Laboratórios
    public function listLabors(){

        $labors = Labors::all();

        if($labors)
        {
            foreach($labors as $labor){

                $data[] = [
                    'name' => $labor->name,
                    'cnpj' => $labor->cnpj
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

    //Lista Usuários do laborátório
    public function listUserLabors(){

        $userLabors = UsersLabors::all();

        if($userLabors)
        {
            foreach($userLabors as $userLabor){

                $data[] = [
                    'id_user' => $userLabor->id_user,
                    'id_labor' => $userLabor->id_labor
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



}
