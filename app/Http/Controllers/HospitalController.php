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


        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/procedencia');

        $items = json_decode($response->getBody());


        /* SEPARA OS DADOS DA API */
        foreach ($items->items as $item) {

            $data[] = [
                'id_api' => $item->codprocedencia,
                'name' => $item->nomeprocedencia,
                'grupo' => $item->grupo,
                'uuid' => $item->uuid
            ];
        }


        /* CASO NÃO TENHA NENHUM HOSPITAL CADASTRADO NO BANCO ELE IRÁ CRIAR*/
        foreach ($data as $save_proc) {
            $group = Groups::where('name', $save_proc['grupo'])->first();

            if ($save_proc['grupo'] === $group->name) {
                $id_group = $group->id;
            }

            try {
                \DB::beginTransaction();

                Hospitais::firstOrCreate(['codprocedencia' => $save_proc['id_api'], 'name' => $save_proc['name'], 'grupo_id' => $id_group, 'uuid' => $save_proc['uuid'],]);


                \DB::commit();
            } catch (\Throwable $th) {
                dd($th->getMessage());
                \DB::rollback();
                return ['error' => 'Could not write data', 400];
            }
        }


        /* LISTA TODOS OS HOSPITAIS APÓS CONSULTA E SALVAR NOVOS DADOS  */
        $hospitals = Hospitais::all();

        if (count($hospitals) > 0) {
            foreach ($hospitals as $hospital) {
                $procedencia[] = [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                    'grupo' => $hospital->grupo_id,
                    'uuid' => $hospital->uuid,
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
