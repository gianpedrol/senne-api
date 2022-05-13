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
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }
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
        $saveLog->id_hospital_atendimento = $hospital->id;
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
        $saveLog->id_hospital_atendimento = $hospital->id;
        $saveLog->save();


        return $response;
    }

    public function listAttendanceDetails($uuid, $atendimento,  Request $request)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento_detalhe/' . $uuid . '/' . $atendimento);


        $hospital = Hospitais::where('uuid', $uuid)->first();

        //GERA LOG
        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 9;
        $saveLog->numatendimento = $atendimento;
        $saveLog->id_hospital_atendimento = $hospital->id;
        $saveLog->save();

        $items = json_decode($response->getBody());
        /* $data = [];
        foreach ($items as $item) {
            if (isset($item[0]->nomepaciente)) {
                $data = [
                    'nomepaciente' =>  $item[0]->nomepaciente
                ];
            }
            //dd($data['nomepaciente']);
            $nomepaciente = $data['nomepaciente'];
            $log = Auth::user();
            $saveLog = new LogsExames();
            $saveLog->id_user = $log->id;
            $saveLog->numatendimento = $atendimento;
            $saveLog->log_description = 'acessou detalhes de atendimento do paciente ' . $nomepaciente  . ' do ' . $hospital->name;
            $saveLog->save();
        }*/
        return $items;
    }

    public function principalReport($uuid, $atendimento,  Request $request)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/laudo/' . $atendimento);

        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 8;
        $saveLog->numatendimento = $atendimento;
        $saveLog->id_hospital_atendimento = $uuid ;
        $saveLog->save();

        return response()->json(['http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/laudo/' . $atendimento], 200);
    }
}
