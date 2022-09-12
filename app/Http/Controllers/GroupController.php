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
use DB;

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
            return response()->json(['error' => "Unauthorized"], 401);
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
                return response()->json(['error' => "Unauthorized"], 401);
        }

        $client = 'A2PsnYpypc_u66U0ANnzfQ..';
        $client_secret = 'M3nxpLJbYPNqkfnkR5tuqg..';
        $resp = Http::withBasicAuth($client, $client_secret)->asForm()->post(
            'http://sistemas.senneliquor.com.br:8804/ords/gateway/oauth/token',
            [
                'grant_type' => 'client_credentials',

            ]
        );

        $token = json_decode($resp->getBody());

        $bearer = $token->access_token;


        /* CONSULTA API DE SISTEMA DA SENNE */
        $token = json_decode($resp->getBody());

        if($request->Order == null){ 
            $request->Order = 'DESC';
        }
        if($request->pageNo == null){ 
            $request->pageNo = 1;
        }

        if($request->pageSize == null){ 
            $request->pageSize = 250;
        }

        $bearer = $token->access_token;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearer
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/procedencia?&Order='.$request->Order.'&PageSize='. $request->pageSize);

        $items = json_decode($response->getBody());


        foreach ($items->Procedencia as $item) {
            foreach ($item->procedencia as $item) {
                $data[] = [
                    'name' => $item->grupo,
                    'codgrupo' => $item->codgrupo
                ];
            }
        }

        /* CASO NÃO TENHA NENHUM GRUPO CADASTRADO NO BANCO ELE IRÁ CRIAR*/
        foreach ($data as $name) {
            $groupCheck =  Groups::where('name', $name['name'])->first();
            // dd(  $groupCheck);
            if($groupCheck){    
                if($groupCheck->codgroup == null){
                    $groupCheck->update(['codgroup' => $name['codgrupo']]);
                }
            }else{
                Groups::create([ 'name' => $name['name'], 'codgroup',  $name['codgrupo']]);
            }

        }
        /* LISTA TODOS OS GRUPOS APÓS CONSULTA E SALVAR NOVOS DADOS  */
        if(empty($request->per_page)){
            $request->per_page =10;
        }
        $groups =  DB::table('groups')->paginate($request->per_page);
        //
        //
        if (count($groups) > 0) {
            foreach ($groups as $group) {
                
                $showGroups[] = [
                    'id' => $group->id,
                    'name' => $group->name,
                    'image' => config('app.url') . 'uploads/' . $group->image
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
        $groups =  DB::table('grooups')->orderBy('id')
        ->paginate(2);
dd($groups);
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
        }

        $data = $request->only(['cnpj', 'image', 'phone', 'email']);

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
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->id_log = 7;
            $saveLog->ip_user = $request->ip();
            $saveLog->save();

            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }


        return response()->json(['msg' => "Edited Successfully!", $group], 200);
    }

    public function getHospitalsGroup($id, Request $request)
    {
        $user_auth = Auth::user();
        $user_group = UsersGroup::from('users_groups as usergroup')
            ->select('usergroup.id_group')
            ->join('groups as group', 'group.id', '=', 'usergroup.id_group')
            ->where('usergroup.id_user', $user_auth->id)
            ->first();

        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized not administrator"], 401);
            }
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

    public function getUsersGroup($id, Request $request)
    {
        $user_auth = Auth::user();
        $user_group = UsersGroup::from('users_groups as usergroup')
            ->select('usergroup.id_group')
            ->join('groups as group', 'group.id', '=', 'usergroup.id_group')
            ->where('usergroup.id_group', $id)
            ->first();
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized not administrator"], 401);
            }
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
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized "], 401);
            }
        }
        $array = ['error' => ''];

        $filename = '';
        $group = Groups::where('id', $request->id_group)->first();
        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $file_name = time() . '-' . $file->getClientOriginalName();
            $file_path = 'uploads/';

            $file->move($file_path, $file_name);

            if ($request->hasFile('image') != "") {
                $filename = $file_name;
            }
        }

        if ($group) {
            $group->image = $filename;
            $group->update();
            return response()->json(
                ['status' => 'success', 'Image uploaded succesfully'],
                200
            );
        } else {
            return response()->json(
                ['error' => 'User Not found'],
                404
            );
        }
    }
}
