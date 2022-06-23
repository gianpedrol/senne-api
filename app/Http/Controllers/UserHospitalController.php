<?php

namespace App\Http\Controllers;

use App\Jobs\sendEmailPasswordReset;
use App\Mail\emailPasswordReset;
use App\Models\Hospitais;
use App\Models\Permissoes;
use App\Models\User;
use App\Models\UserLog;
use App\Models\UserPermissoes;
use App\Models\UsersHospitals;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class UserHospitalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');

        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        /* 1 = Administrador Senne | 2 = User Labor | 3 = User Hospital | 4 = Médico | 5 = Paciente */
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }
    }
    /* RETORNA APENAS HOSPITAL SELECIONADO */
    public function getHospital($id)
    {
        $hospital = Hospitais::where('id', $id)->first();

        return $hospital;
    }

    public function updateUserHospital($id, Request $request)
    {
        $data = $request->only(['name', 'cpf', 'image', 'phone']);

        //atualizando o item
        $user = User::find($id);
        if ($user) {

            $user->update($data);

            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->Log = 'Usuário Atualizou um Usuário';
            $saveLog->save();

            return response()->json(['error' => "Edited Successfully!", $user], 200);
        } else {
            $array['message'] = 'The User can t be found';
        }
    }

    public function showUserGroup($id)
    {
        $user = User::where('id', $id)->first();

        return $user;
    }

}
