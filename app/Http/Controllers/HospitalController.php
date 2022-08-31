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
use App\Models\UserLog;
use App\Models\UsersHospitals;
use App\Models\UserPermissoes;
use PHPUnit\TextUI\XmlConfiguration\Group;
use App\Models\UsersGroup;
use DB;

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
    /**
     * @OA\Get(
     *   tags={"List All Hospitals "},
     *   path="/api/list/procedencia",
     *   summary="Summary",  
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function getProcedencia(Request $request)
    {

        /* 1 = Administrador Senne | 2 = Usuario */
        if (auth()->user()->role_id != 1) {
            if ($request->user()->permission_user($request->user()->id, 2)) {
                return response()->json(['error' => "Unauthorized"], 401);
            }
            if ($request->user()->permission_user($request->user()->id, 3)) {
                return response()->json(['error' => "Unauthorized"], 401);
            }
            return response()->json(['error' => 'Unauthorized access'], 401);
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
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/procedencia?&PageNo='. $request->pageNo .'&Order='.$request->Order.'&PageSize='. $request->pageSize);

        $items = json_decode($response->getBody());
        foreach ($items->Procedencia as $item) {
           
            foreach ($item->procedencia as $item) {
                $data[] = [
                    'id_api' => $item->codprocedencia,
                    'name' => $item->nomeprocedencia,
                    'grupo' => $item->grupo,
                    'uuid' => $item->uuid,
                    'codgrupo' => $item->codgrupo
                ];
            }
        }

        /*/* CASO NÃO TENHA NENHUM HOSPITAL CADASTRADO NO BANCO ELE IRÁ CRIAR*/
        foreach ($data as $save_proc) {

            if ($save_proc['codgrupo'] == null) {
                $save_proc['codgrupo'] = 1;
            }
            $groups = Groups::where('codgroup', $save_proc['codgrupo'])->get();
            
            foreach ($groups as $group){
                $id_group = $group->id;
            }
            try {
                \DB::beginTransaction();

                 Hospitais::where('uuid', $save_proc['uuid'])->update(['name' => $save_proc['name']]); 
                 Hospitais::updateOrCreate(['name' => $save_proc['name']],['codprocedencia' => $save_proc['id_api']] ,  ['grupo_id' =>$id_group], ['uuid' => $save_proc['uuid']]);              

                \DB::commit();
            } catch (\Throwable $th) {
                dd($th->getMessage());
                \DB::rollback();
                return ['error' => 'Could not write data', 400];
            }
        }

        if(empty($request->paginate)){
            $request->paginate = 10;
        }

        /* LISTA TODOS OS HOSPITAIS APÓS CONSULTA E SALVAR NOVOS DADOS  */
        $hospitals = DB::table('hospitais')->paginate( $request->paginate);

        if (count($hospitals) > 0) {
            foreach ($hospitals as $hospital) {
                $procedencia[] = [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                    'grupo' => $hospital->grupo_id,
                    'uuid' => $hospital->uuid,
                    'codprocedencia' => $hospital->codprocedencia
                ];
            }

            return response()->json(
                ['status' => 'success', 'Hospitals' => $hospitals],
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
        /* 1 = Administrador Senne | 2 = Usuario */
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        $data = $request->only('name', 'email', 'cnpj', 'image', 'phone', 'grupo_id');

        if (empty($data['grupo_id'])) {
            return response()->json(['error' => 'grupo_id cant be null'], 400);
        }

        if (!empty($data['name'])) {
            $hospital_db = Hospitais::where('email', $data['email'])->first();
            if (!empty($hospital_db)) {
                return response()->json(['message' => 'Hospital email already exists!'], 400);
            }
        }
        try {
            \DB::beginTransaction();
            //Define
            $newHospital = new Hospitais();
            $newHospital->name = $data['name'];
            $newHospital->email =  $data['email'];
            $newHospital->image = $data['image'];
            $newHospital->cnpj =  $data['cnpj'];
            $newHospital->phone = $data['phone'];
            $newHospital->grupo_id = $data['grupo_id'];
            $newHospital->save();

            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->Log = 'Usuário Criou um Hospital';
            $saveLog->save();

            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }



        return response()->json(['message' => 'Hospital create successfully'], 200);
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
        /* 1 = Administrador Senne | 2 = Usuario */
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }
        if (!$request->user()->permission_user($request->user()->id, 2)) {
            return response()->json(['error' => "Unauthorized"], 401);
        }
        if (!$request->user()->permission_user($request->user()->id, 3)) {
            return response()->json(['error' => "Unauthorized"], 401);
        }

        $data = $request->only('name', 'email', 'cnpj', 'image', 'phone', 'grupo_id');

        if (empty($data['name'])) {
            return response()->json(['error' => "Name cannot be null"], 200);
        }
        //atualizando o HOSPITAL
        $hospital = Hospitais::where('id', $id)->first();
        if ($hospital) {

            $hospital->update($data);

            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->Log = 'Usuário Atualizou um Hospital';
            $saveLog->save();

            return response()->json(['message' => "Edited Successfully!", $hospital], 200);
        } else {
            return response()->json(['error' => "The Hospital  can t be found"], 404);
        }
    }
}
