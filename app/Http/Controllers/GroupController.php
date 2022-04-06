<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Groups;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Hospitais;
use App\Models\UsersHospitals;
use App\Models\UserPermissoes;
use PHPUnit\TextUI\XmlConfiguration\Group;
use App\Models\UsersGroup;

class GroupController extends Controller

{

    public function __construct()
    {
        $this->middleware('auth:api');

        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        /* 1 = Administrador Senne | 2 = Usuario */
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }
    }

    //
    public function index()
    {
        $groups = Groups::all();
        return response()->json($groups);
    }

    //Salva Grupo no DB
    public function storeGroup(Request $request)
    {

        $data = $request->only('name', 'cnpj', 'image', 'phone');

        //Definimos ID do user como 2 (Usuário Hospital)
        $role_id = 2;

        if (!empty($data['id_pai'])) {
            $group_db = Groups::where('id_api', $data['id_pai'])->first();
            if (!empty($group_db)) {
                return response()->json(['message' => 'Group already exists!'], 400);
            }
        }

        $nameGroup = $data['name'];
        $cnpjGroup = $data['cnpj'];
        $imageGroup = $data['image'];
        $phoneGroup = $data['phone'];

        $group_db = Groups::where('name', $data['name'])->first();
        if ($group_db) {
            return response()->json(['message' => 'Group already exists!'], 400);
        } else {
            //Aqui criamos o registro no banco de dados
            $newGroup = new Groups();
            $newGroup->name = $nameGroup;
            $newGroup->cnpj = $cnpjGroup;
            $newGroup->image = $imageGroup;
            $newGroup->phone = $phoneGroup;
            $newGroup->save();

            //Define
            $senha_temp = bcrypt(md5('123456'));
            $newUser = new User();
            $newUser->name = $data['name'];
            $newUser->cpf = $data['cnpj'];
            $newUser->phone = $data['phone'];
            $newUser->email = $data['email'];
            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            $newUser->save();

            return response()->json(['message' => 'Group create successfully', $newGroup], 200);
        }
    }


    public function getGroups()
    {
        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/procedencia');

        $items = json_decode($response->getBody());

        /* SEPARA OS DADOS DA API */
        foreach ($items->items as $item) {

            $data[] = [
                'grupo' => $item->grupo
            ];
        }

        /* CASO NÃO TENHA NENHUM GRUPO CADASTRADO NO BANCO ELE IRÁ CRIAR*/
        foreach ($data as $name) {
            Groups::firstOrCreate(['name' => $name['grupo']]);
        }

        /* LISTA TODOS OS GRUPOS APÓS CONSULTA E SALVAR NOVOS DADOS  */
        $groups =  Groups::all();

        if (count($groups) > 0) {
            foreach ($groups as $group) {

                $showGroups[] = [
                    'id' => $group->id,
                    'name' => $group->name,
                ];
            }

            return response()->json(
                ['status' => 'success',  'Grupos' => $groups],
                200
            );
        } else {
            return response()->json(
                ['status' => 'Groups is empty!'],
                404
            );
        }
    }

    public function updateGroup($id, Request $request)
    {
        $data = $request->only(['name', 'cnpj', 'image', 'phone']);

        //atualizando o item
        $group = Groups::find($id);
        if ($group) {
            if ($data) {
                $group->name = $data['name'];
                $group->cnpj = $data['cnpj'];
                $group->image = $data['image'];
                $group->phone = $data['phone'];
            }
            $group->save();
            return response()->json(['message' => "Edited Successfully!", $group], 200);
        } else {
            $array['error'] = 'The Group ' . $id . ' can t be found';
            return $array;
        }
    }



    public function getHospitalsGroup(Request $request)
    {
        $group = Groups::find($request->id);


        if (!$group) {
            return response()->json([
                'message'   => 'The Group can t be found',
            ], 404);
        } else {
            $hospitals = $group->hospitals;

            return response()->json(
                ['status' => 'success', $group],
                200
            );
        }
    }

    //Salva usuário Grupo
    public function storeUser(Request $request)
    {

        //Definimos ID do user como 2 (Usuário Hospital)
        $role_id = 2;

        $data = $request->only(['name', 'telefone', 'cpf', 'id_group']);

        $user = User::where('cpf', $data['cpf'])->first();

        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
        }

        //Define
        $senha_temp = bcrypt(md5('123456'));

        $newUserGroup = new User();
        $newUserGroup->name = $data['name'];
        $newUserGroup->cpf = $data['cpf'];
        $newUserGroup->role_id = $role_id;
        $newUserGroup->password = $senha_temp;
        $newUserGroup->save();


        $userGroup = new UsersGroup();
        $userGroup->id_user = $newUserGroup->id;
        $userGroup->id_Group = $data['id_group'];
        $userGroup->save();
        
        return response()->json(['message' => "User registered successfully!"], 200);
    }
}
