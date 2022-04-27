<?php

namespace App\Http\Controllers;

use App\Models\Hospitais;
use Illuminate\Http\Request;
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
    public function listAttendance($uuid, $atendimento)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento/' . $uuid . '/' . $atendimento);



        $items = json_decode($response->getBody());

        return $items;
    }

    public function listAttendanceDate($uuid, $startdate, $finaldate)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/proced_atendimentos/' . $uuid . '/' . $startdate . '/' . $finaldate);



        $items = json_decode($response->getBody());

        return $items;
    }

    public function listAttendanceDetails($uuid, $atendimento)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento_detalhe/' . $uuid . '/' . $atendimento);



        $items = json_decode($response->getBody());

        return $items;
    }

    public function principalReport($atendimento)
    {
        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/laudo/' . $atendimento);


        return $response;
    }
}
