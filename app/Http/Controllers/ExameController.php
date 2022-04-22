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
    public function listAtendimentos($uuid, $atendimento)
    {

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento/' . $uuid . '/' . $atendimento);



        $items = json_decode($response->getBody());

        return $items;
    }

    public function listAtendimentosDate(Request $request)
    {
        $query = Hospitais::query();

        if ($request->has('date_start')) {
            $query->where('nome', 'LIKE', '%' . $request->date_start . '%');
        }

        $uuid = $request->query('uuid');
        $atendimento = $request->query('atendimento');

        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento/' . $uuid . '/' . $atendimento);



        $items = json_decode($response->getBody());

        return $items;
    }
}
