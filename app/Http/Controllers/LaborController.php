<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\UsersLabors;
use App\Models\User;
use App\Models\Labors;

class LaborController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');

    	/* 1 = Administrador Senne | 2 = User Labor | 3 = User Hospital | 4 = Médico | 5 = Paciente */
        if(auth()->user()->role_id != 1) {
            return response()->json(['error'=>'Unauthorized access'],401);
        }
        
    }


    public function store(Request $request){

        $data= $request->only('name');


        $labor= Labors::create(['name'=>$data['name']]);
        //UsersLabors::create(['id_labor'=>$labor->id, 'id_user'=>$user->id]);

        return response()->json(['message'=>'Labor create successfully'],200);
    	
    }


    public function storeUser(Request $request){

        //Definimos ID do user como 2 (Usuário laboratorio)
        $role_id= 2;

        $data = $request->only(['name','telefone','cpfcnpj']);

        $user = User::where('cpfcnpj', $data['cpfcnpj'])->first();

        if(!empty($user)){
          return response()->json(['error'=>"User already exists!"],200);
        }

        //Define
        $senha_temp= bcrypt(md5('123456'));

        $newUser = new User();
        $newUser->name = $data['name'];
        $newUser->cpfcnpj = $data['cpfcnpj'];
        $newUser->role_id = $role_id;
        $newUser->password = $senha_temp;

        $newUser->save();

        return response()->json(['message'=>"User registered successfully!"],200);

    }


}
