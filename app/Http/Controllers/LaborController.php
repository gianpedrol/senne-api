<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use App\Models\User;

class LaborController extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth:api');


        //dd(auth()->user()->role_id);

        
    }


    public function store(Request $request){

    	/*$user= User::where('id',$request->user_id)->first();

        if(!$user){
            return response()->json(['message'=>'user not found'],404);
        }*/

    	//dd($user);

    	/* 1 = Administrador Senne | 2 = User Labor | 3 = User Hospital | 4 = Médico | 5 = Paciente */
        /*if($user->role_id != 1) {
            return response()->json(['message'=>'Unauthorized access'],401);
        }

    	dd('AQUI');*/
    }


}
