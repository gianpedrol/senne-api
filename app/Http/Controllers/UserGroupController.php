<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Hospitais;
use App\Models\UserLog;
use App\Models\UsersHospitals;
use App\Models\UserPermissoes;
use App\Models\UsersGroup;
use Illuminate\Support\Facades\Http;
use PHPUnit\TextUI\XmlConfiguration\Group;

class UserGroupController extends Controller
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

    //Salva usuário Grupo
    public function storeUserGroup(Request $request)
    {


        $data = $request->only(['name', 'email', 'telefone', 'cpf', 'id_group']);

        if (empty($data['email'])) {
            return response()->json(['error' => "E-mail cannot be null!"], 200);
        }

        if (empty($data['id_group'])) {
            return response()->json(['error' => "Id group cannot be null!"], 200);
        }

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
        }


        try {
            \DB::beginTransaction();


            //Define
            //$senha_temp = bcrypt(md5('123456'));
            $senha_temp = bcrypt('123456789');

            //Definimos ID do user como 2 (Usuário Grupo)
            $role_id = 2;
            $newUserGroup = new User();
            $newUserGroup->name = $data['name'];
            $newUserGroup->email = $data['email'];
            $newUserGroup->cpf = $data['cpf'];
            $newUserGroup->role_id = $role_id;
            $newUserGroup->password = $senha_temp;
            $newUserGroup->save();


            //Definimos id da permissão como administrador de um grupo
            $id_permissao = 1;
            $userGroup = new UsersGroup();
            $userGroup->id_user = $newUserGroup->id;
            $userGroup->id_Group = $data['id_group'];
            $userGroup->id_permissao = $id_permissao;
            $userGroup->save();

            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->Log = 'Usuário Criou um usuário para o grupo';
            $saveLog->save();


            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }



        return response()->json(['message' => "User registered successfully!"], 200);
    }


    public function listUsersGroup(Request $request)
    {



        $users = User::all();

        if ($users) {
            foreach ($users as $user) {

                $data[] = [
                    'name' => $user->name,
                    'cpf' => $user->cpf,
                    'email' => $user->email,
                    'telefone' => $user->telefone,
                ];
            }

            return response()->json(
                ['status' => 'success', 'data' => $data],
                200
            );
        } else {
            return response()->json(
                ['status' => 'labor not found'],
                400
            );
        }
    }

    public function showUserGroup($id)
    {
        $user = User::where('id', $id)->first();

        return $user;
    }

    public function updateUserGroup(Request $request, $id)
    {

        $data = $request->only(['name', 'cpf', 'email', 'permissao']);

        //atualizando o Usuário do Grupo
        $user = User::where('id', $id)->first();
        if ($user) {

            $user->update($data);

            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->Log = 'Usuário Atualizou um Usuário';
            $saveLog->save();

            return response()->json(['message' => "Edited Successfully!", $user], 200);
        } else {
            return response()->json(['error' => "The User can t be found"], 404);
        }
    }
}
