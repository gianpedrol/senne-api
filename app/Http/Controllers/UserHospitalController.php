<?php

namespace App\Http\Controllers;

use App\Models\Hospitais;
use App\Models\Permissoes;
use App\Models\User;
use App\Models\UserLog;
use App\Models\UserPermissoes;
use App\Models\UsersHospitals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    //salva usuario hospital
    public function storeUserHospital(Request $request)
    {

        //Definimos ID do user como 2 (Usuário Hospital)
        $role_id = 2;

        //Definimos o tipo do usuario por padrão administrador
        $nivel_user = 1;

        $data = $request->only(['name', 'phone', 'cpf', 'email', 'id_hospital']);

        if (empty($data['id_hospital'])) {
            return response()->json(['error' => 'ID Hospital cannot be empty'], 404);
        } else if (empty($this->getHospital($data['id_hospital']))) {
            return response()->json(['error' => 'Hospital not found'], 404);
        }

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
        }

        try {
            \DB::beginTransaction();
            //Define
            $senha_temp = bcrypt(md5('123456'));

            $newUser = new User();
            $newUser->name = $data['name'];
            $newUser->cpf = $data['cpf'];
            $newUser->phone = $data['phone'];
            $newUser->email = $data['email'];
            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            $newUser->save();

            $userHospital = new UsersHospitals();
            $userHospital->id_user = $newUser->id;
            $userHospital->id_hospital = $data['id_hospital'];
            $userHospital->save();

            $userPermissoes = new Permissoes();
            $userPermissoes->nivel = 1;
            $userPermissoes->save();


            $userPermissao = new UserPermissoes();
            $userPermissao->id_user = $newUser->id;
            $userPermissao->id_permissao = $nivel_user;
            $userPermissao->save();

            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->Log = 'Usuário Criou um usuário';
            $saveLog->save();


            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }




        return response()->json(['message' => "User registered successfully!"], 200);
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
    public function getUsersHospital(Request $request)
    {
        $hospital = Hospitais::find($request->id);

        if (!$hospital) {
            return response()->json([
                'message'   => 'The Hospital can t be found',
            ], 404);
        } else {

            $users = $hospital->users_hospitals;
            $data = [];

            foreach ($users as $user) {
                $data[] = $user->usersHospital;
            }

            return response()->json(
                ['status' => 'success', $data],
                200
            );
        }
    }
}