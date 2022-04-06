<?php

namespace App\Http\Controllers;

use App\Models\Groups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;
use App\Models\User;
use App\Models\Hospitais;
use App\Models\Permissoes;
use App\Models\UsersHospitals;
use App\Models\UserPermissoes;
use PHPUnit\TextUI\XmlConfiguration\Group;
use App\Models\UsersGroup;


class HospitalController extends Controller
{
    public function __construct(Groups $grupo)
    {
        $this->middleware('auth:api');

        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        /* 1 = Administrador Senne | 2 = Usuario */
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        /* PARA TRAZER RELACIONADOS AO MODEL */
        $this->groupsModel = $grupo;
    }

    /*
        RECEBE API E SALVA NO BANCO    
    */
    public function getProcedencia()
    {
        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/procedencia');

        $items = json_decode($response->getBody());




        /* SEPARA OS DADOS DA API */
        foreach ($items->items as $item) {
            // $grupo_id = Groups::where('name')->get();

            $data[] = [
                'id_api' => $item->codprocedencia,
                'name' => $item->nomeprocedencia,
                'grupo' => $item->grupo
            ];
        }

        /* CASO NÃO TENHA NENHUM HOSPITAL CADASTRADO NO BANCO ELE IRÁ CRIAR*/
        foreach ($data as $name) {

            Hospitais::firstOrCreate(['name' => $name['name']]);
        }


        /* LISTA TODOS OS HOSPITAIS APÓS CONSULTA E SALVAR NOVOS DADOS  */
        $hospitals = Hospitais::all();

        if (count($hospitals) > 0) {
            foreach ($hospitals as $hospital) {

                $procedencia[] = [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                    'grupo' => $hospital->grupo_id
                ];
            }

            return response()->json(
                ['status' => 'success', 'Hospitals' => $procedencia],
                200
            );
        } else {
            return response()->json(
                ['status' => 'hospital is empty!'],
                404
            );
        }
    }

    /* FIM DE RECEBIMENTO API */

    //Salva Hospital
    public function storeHospital(Request $request)
    {

        $data = $request->only('name', 'email', 'cnpj', 'image', 'phone', 'grupo_id');

        if (!empty($data['name'])) {
            $hospital_db = Hospitais::where('email', $data['email'])->first();
            if (!empty($hospital_db)) {
                return response()->json(['message' => 'Hospital email already exists!'], 400);
            }
        }


        Hospitais::create(['name' => $data['name'], 'email' => $data['email'], 'cnpj' => $data['cnpj'], 'image' => $data['phone'], 'grupo_id' => $data['grupo_id']]);

        return response()->json(['message' => 'Hospital create successfully'], 200);
    }


    //LISTA HOSPITAIS CADASTRADOS EM NOSSO BANCO DE DADOS
    public function listHospitals()
    {

        /* 1 = Administrador Senne | 2 = User Labor | 3 = User Hospital | 4 = Médico | 5 = Paciente */
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized access!'], 401);
        }

        $hospitals = Hospitais::all();

        if (count($hospitals) > 0) {
            foreach ($hospitals as $hospital) {

                $data[] = [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                    'grupo_id' => $hospital->grupo_id
                ];
            }

            return response()->json(
                ['status' => 'success', 'data' => $data],
                200
            );
        } else {
            return response()->json(
                ['status' => 'hospital is empty!'],
                404
            );
        }
    }

    /* RETORNA APENAS HOSPITAL SELECIONADO */
    public function getHospital($id)
    {
        $hospital = Hospitais::where('id', $id)->first();

        return $hospital;
    }

    //FAZ ATUALIZAÇÃO DO HOSPITAL 
    public function updateHospital($id, Request $request)
    {
        $data = $request->only('name', 'email', 'cnpj', 'image', 'phone', 'grupo_id');

        //atualizando o HOSPITAL
        $hospital = Hospitais::where('id', $id)->first();
        if ($hospital) {
            if ($data) {
                $hospital->name = $data['name'];
                $hospital->email = $data['email'];
                $hospital->cnpj = $data['cnpj'];
                $hospital->image = $data['image'];
                $hospital->phone = $data['phone'];
                $hospital->grupo_id = $data['grupo_id'];
            }
            $hospital->save();
            return response()->json(['error' => "Edited Successfully!", $hospital], 200);
        } else {
            return 'The Hospital  can t be found';
        }
    }

    //salva usuario hospital
    public function storeUserHospital(Request $request)
    {

        //Definimos ID do user como 2 (Usuário Hospital)
        $role_id = 2;

        //Definimos o tipo do usuario por padrão administrador
        $nivel_user = 1;

        $data = $request->only(['name', 'phone', 'cpf', 'email', 'id_hospital', 'id_group']);

        if (empty($data['id_hospital'])) {
            return response()->json(['error' => 'ID Hospital cannot be empty'], 404);
        } else if (empty($this->getHospital($data['id_hospital']))) {
            return response()->json(['error' => 'Hospital not found'], 404);
        }

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
        }

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


        return response()->json(['message' => "User registered successfully!"], 200);
    }
    public function updateUserHospital($id, Request $request)
    {
        $data = $request->only(['name', 'cpf', 'image', 'phone']);

        //atualizando o item
        $user = User::find($id);
        if ($user) {
            if ($data) {
                $user->name = $data['name'];
                $user->cnpj = $data['cpf'];
                $user->image = $data['image'];
                $user->phone = $data['phone'];
            }
            $user->save();
            return response()->json(['error' => "Edited Successfully!", $user], 200);
        } else {
            $array['message'] = 'The User can t be found';
        }
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
