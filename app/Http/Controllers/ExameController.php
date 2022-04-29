<?php

namespace App\Http\Controllers;

use App\Models\Hospitais;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ExameController extends Controller
{

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

        //GERA LOG
        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 10;
        $saveLog->save();
        $items = json_decode($response->getBody());

        $items = json_decode($response->getBody());

        return $items;
    }

    public function listAttendanceDate($uuid, $startdate, $finaldate)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/proced_atendimentos/' . $uuid . '/' . $startdate . '/' . $finaldate);





        return $items;
    }

    public function listAttendanceDetails($uuid, $atendimento,  Request $request)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento_detalhe/' . $uuid . '/' . $atendimento);


        //GERA LOG
        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 9;
        $saveLog->save();

        $items = json_decode($response->getBody());

        return $items;
    }

    public function principalReport($atendimento,  Request $request)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/laudo/' . $atendimento);


        //GERA LOG
        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 8;
        $saveLog->save();

        return 'http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/laudo/' . $atendimento;
    }
}
