<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ScheduleController extends Controller
{
    public function scheduleSearchUser(Request $request){

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
    ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/agenda_busca_paciente?Hash=' . $request->uuid . '&NomePaciente='.$request->paciente);
        
        $return = json_decode($response->getBody());
        
        return $return;
    }

    public function scheduleCIDs(Request $request){
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

        if($request->pageNo == null){ 
            $request->pageNo = 1;
        }

        if($request->pageSize == null){ 
            $request->pageSize = 10;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearer
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/agenda_cid?PageNo='. $request->pageNo.'&PageSize='.$request->pageSize.'&pesquisa=paciente');

       $return = json_decode($response->getBody());

       return $return;
    }

    public function scheduleConvenios(Request $request){
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

        $loggedUser = Auth::user();
        $tipo = $loggedUser->role_id;  
        
        if($loggedUser->role_id == 2 ){
            $tipo =1;
        }  
        if($loggedUser->role_id == 3 ){
            $tipo =5;
        }  
        if($request->Order == null){ 
            $request->Order = 'DESC';
        }
        if($request->pageNo == null){ 
            $request->pageNo = 1;
        }

        if($request->pageSize == null){ 
            $request->pageSize = 10;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearer
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/agenda_convenio?Hash=' . $request->uuid . '&PageNo='. $request->pageNo . '&Tipo=' . $tipo);

       $return = json_decode($response->getBody());

       return $return;
    }

}
