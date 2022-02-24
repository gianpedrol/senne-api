<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Hospitais;

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


    public function storeHospital(Request $request){

        $data= $request->only('name', 'id_api');


        Hospitais::create(['name'=>$data['name'], 'id_api'=>$data['id_api']]);

        return response()->json(['message'=>'Hospital create successfully'],200);
    	
    }
}
