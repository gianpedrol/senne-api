<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ScheduleController extends Controller
{
    public function scheduleSearchUser(){

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

              
      $response = Http:: withHeaders([
        'Authorization' => 'Bearer ' . $bearer
    ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/agenda_busca_paciente?Hash=DD1793AAC82EC34BE053E600A8C0C7AE&NomePaciente=Paciente');
        
        $return = json_decode($response->getBody());
        
        return $return;
    }

    public function scheduleCIDs(){
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

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearer
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/agenda_cid?PageNo=10&PageSize=10&pesquisa=paciente');

       $return = json_decode($response->getBody());

       return $return;
    }

    public function scheduleConvenios(){
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

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearer
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/atend_detalhe?Hash=DD1793AAC81CC34BE053E600A8C0C7AE&NomePacientes=');

       $return = json_decode($response->getBody());

       return $return;
    }

}
