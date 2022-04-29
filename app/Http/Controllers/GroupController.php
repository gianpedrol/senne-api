<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Groups;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Hospitais;
use App\Models\UserLog;
use App\Models\UsersHospitals;
use App\Models\UserPermissoes;
use PHPUnit\TextUI\XmlConfiguration\Group;
use App\Models\UsersGroup;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;


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


    //Salva Grupo no DB
    public function storeGroup(Request $request)
    {
        /* 1 = Administrador Senne | 2 = Usuario */
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        $data = $request->only('name', 'cnpj', 'image', 'phone');


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
        try {
            \DB::beginTransaction();


            //Define
            $newGroup = new Groups();
            $newGroup->name = $nameGroup;
            $newGroup->image = $imageGroup;
            $newGroup->cnpj =  $cnpjGroup;
            $newGroup->phone = $phoneGroup;
            $newGroup->save();


            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->Log = 'Usuário Criou um Grupo';
            $saveLog->save();

            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }



        $group_db = Groups::where('name', $data['name'])->first();
        if ($group_db) {
            return response()->json(['message' => 'Group already exists!'], 400);
        } else {
            return response()->json(['message' => 'Group create successfully', $newGroup], 200);
        }
    }


    public function getGroups(Request $request)
    {
        /* 1 = Administrador Senne | 2 = Usuario */
        if (auth()->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 2)) {
                return response()->json(['error' => "Unauthorized"], 401);
            }
            if (!$request->user()->permission_user($request->user()->id, 3)) {
                return response()->json(['error' => "Unauthorized"], 401);
            }
            return response()->json(['error' => 'Unauthorized access'], 401);
        }
        /* 
            Função que checa se o usuario tem permissão para acessar este método.
            ## Params ##
            $id_user : passa o id do usuario
            $id_permissão : passa o id da view { 2 -> para view de agendamentos, 3 -> para view de consultas }
         */

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
    public function listGroups(Request $request)
    {

        /* 
            Função que chega se o user é usuario Senne ou Usuario comum
         */
        if (!$request->user()->role_id != 1) {
            return response()->json(['error' => "Unauthorized"], 401);
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
        $user_auth = Auth::user();
        $user_group = UsersGroup::from('users_groups as usergroup')
            ->select('usergroup.id_group')
            ->join('groups as group', 'group.id', '=', 'usergroup.id_group')
            ->where('usergroup.id_user', $user_auth->id)
            ->first();

        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized "], 401);
            }
            if ($user_group->id_group != $id) {
                return response()->json(['error' => "Unauthorized "], 401);
            }
        }

        $data = $request->only(['cnpj', 'image', 'phone']);

        if (empty($data['cnpj'])) {
            return response()->json(['error' => "cnpj cannot be null"], 200);
        }


        //dd($group);

        try {
            \DB::beginTransaction();
            //atualizando o item
            $group = Groups::find($id);
            $group->update($data);

            //GERA LOG
            $log = Auth::user();
            dd($log->id);
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->id_log = 7;
            $saveLog->save();

            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }

        return response()->json(['msg' => "Edited Successfully!", $group], 200);
    }



    public function getHospitalsGroup(Request $request)
    {
        if (!$request->user()->permission_user($request->user()->id, 2)) {
            return response()->json(['error' => "Unauthorized"], 401);
        }
        if (!$request->user()->permission_user($request->user()->id, 3)) {
            return response()->json(['error' => "Unauthorized"], 401);
        }

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



    public function getUsersGroup(Request $request)
    {
        if (!$request->user()->permission_user($request->user()->id, 2)) {
            return response()->json(['error' => "Unauthorized"], 401);
        }
        if (!$request->user()->permission_user($request->user()->id, 3)) {
            return response()->json(['error' => "Unauthorized"], 401);
        }

        $group = Groups::find($request->id);

        if (!$group) {
            return response()->json([
                'message'   => 'The Hospital can t be found',
            ], 404);
        } else {

            $users = $group->usersGroup;

            $data = [];

            foreach ($users as $user) {
                $data[] = $user->users_group;
            }

            return response()->json(
                ['status' => 'success', $data],
                200
            );
        }
    }

    public function updateImageGroup(Request $request)
    {
        $array = ['error' => ''];


        $imageGroup = $request->file('image');

        $dest = public_path('media/groups/');
        $image_name = md5(time() . rand(0, 9999)) . '.jpg';

        $img = Image::make($imageGroup->getRealPath());
        $img->fit(300, 300)->save($dest . '/' . $image_name);

        $group = Groups::where('id', $request->id_group)->first();

        if ($group) {
            $group->image = $image_name;
            $group->update();
            return response()->json(
                ['status' => 'success', 'Image uploaded succesfully'],
                200
            );
        } else {
            return response()->json(
                ['error' => 'Group Not found'],
                404
            );
        }
    }
}
