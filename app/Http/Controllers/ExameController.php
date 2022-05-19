<?php

namespace App\Http\Controllers;

use App\Models\Hospitais;
use App\Models\LogsExames;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ExameController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');

        if (!auth()->user()) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        /* 1 = Administrador Senne | 2 = Usuario */
        /*   if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }*/
    }

    public function listExame()
    {
        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/exame');

        $items = json_decode($response->getBody());

        return $items;
    }

    public function resultExame(Request $request)
    {
        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::post('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/resultado', [
            'NumeroCPF' => $request->cpf,
            'DataNascimento' => $request->datanascimento
        ]);

        $items = json_decode($response->getBody());

        return $items;
    }
    public function listAttendance($uuid, $atendimento,  Request $request)
    {

        
        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento/' . $uuid . '/' . $atendimento);

        $hospital = Hospitais::where('uuid', $uuid)->first();

        $items = json_decode($response->getBody());

        //GERA LOG
        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 9;
        $saveLog->numatendimento = $atendimento;
        $saveLog->uuid_hospital_atendimento = $uuid;
        $saveLog->save();

        return $items;
    }

    public function listAttendanceDate($uuid, $startdate, $finaldate, Request $request)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/proced_atendimentos/' . $uuid . '/' . $startdate . '/' . $finaldate);

        $hospital = Hospitais::where('uuid', $uuid)->first();

        //GERA LOG
        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 10;
        $saveLog->uuid_hospital_atendimento = $uuid;
        $saveLog->save();


        return $response;
    }

    public function listAttendanceDetails($uuid, $atendimento,  Request $request)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get(
            'http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento_detalhe/' . $uuid . '/' . $atendimento
        );


        // dd($parsed);
        $hospital = Hospitais::where('uuid', $uuid)->first();

        //GERA LOG
        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 9;
        $saveLog->numatendimento = $atendimento;
        $saveLog->uuid_hospital_atendimento = $uuid;
        $saveLog->save();

        $items = $response->getBody();
        return $items;
    }

    public function principalReport($uuid, $atendimento, $r_id, Request $request)
    {

        // dd($uuid);laudocplt/:r_id
        /* CONSULTA API DE SISTEMA DA SENNE */
        // $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/laudocplt/' . $r_id);

        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 8;
        $saveLog->numatendimento = $atendimento;
        $saveLog->uuid_hospital_atendimento = $uuid;
        $saveLog->save();

        return response()->json(['http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/laudocplt/' . $r_id], 200);
    }
}
