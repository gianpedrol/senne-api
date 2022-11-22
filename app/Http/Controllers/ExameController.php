<?php

namespace App\Http\Controllers;

use App\Mail\SolicitationAddExam;
use App\Models\Hospitais;
use App\Models\LogsExames;
use App\Models\ObservationsAttedance;
use Illuminate\Support\Facades\Mail;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Exception;

class ExameController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        
        if (!auth()->user()) {
            return response()->json(['message' => 'Unauthorized access'], 401);
        }


    }

    public function listExame()
    {
    /*    /* CONSULTA API DE SISTEMA DA SENNE */

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
            ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/medicos');
            return $response;
    }

    public function resultExame(Request $request)
    {
        /* CONSULTA API DE SISTEMA DA SENNE */
        $response = Http::post('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/resultado', [
            'NumeroCPF' => $request->cpf,
            'DataNascimento' => $request->datanascimento
        ]);

        $items = json_decode($response->getBody());

        return $items;
    }


    public function listAttendance($uuid, $atendimento,  Request $request)
    {

        $client = 'A2PsnYpypc_u66U0ANnzfQ..';
        $client_secret = 'M3nxpLJbYPNqkfnkR5tuqg..';
        $resp = Http::withBasicAuth($client, $client_secret)->asForm()->post(
            'http://sistemas.senneliquor.com.br:8804/ords/gateway/oauth/token',
            [
                'grant_type' => 'client_credentials',

            ]
        );

        $token = json_decode($resp->getBody());

        $loggedUser = Auth::user();
        $tipo = $loggedUser->role_id;    


        if($request->Order == null){ 
            $request->Order = 'DESC';
        }
        if($request->pageNo == null){ 
            $request->pageNo = 1;
        }
        if($loggedUser->role_id == 3 ){
            $tipo =5;
        }  

        if($request->pageSize == null){ 
            $request->pageSize = 250;
        }

        $bearer = $token->access_token;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearer
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/atend_exames?Tipo='.$tipo.'&NumAtendimento='.$atendimento.'&Acesso='. $uuid. '&Tipo='.$tipo. '&PageNo='. $request->pageNo .'&Order='.$request->Order.'&PageSize='. $request->pageSize .'&NomeExame='.$request->NomeExame .'&NomeMedico='.$request->NomeMedico.'&NomePaciente='.$request->NomePaciente
    );
    
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

    public function listAttendanceDate($uuid,$startdate, $finaldate, Request $request)
    {



        $client = 'A2PsnYpypc_u66U0ANnzfQ..';
        $client_secret = 'M3nxpLJbYPNqkfnkR5tuqg..';
        $resp = Http::withBasicAuth($client, $client_secret)->asForm()->post(
            'http://sistemas.senneliquor.com.br:8804/ords/gateway/oauth/token',
            [
                'grant_type' => 'client_credentials',

            ]
        );

       

        $loggedUser = Auth::user();
        $tipo = $loggedUser->role_id;    

        if($request->Order == null){ 
            $request->Order = 'DESC';
        }
        if($request->pageNo == null){ 
            $request->pageNo = 1;
        }

        if($loggedUser->role_id == 3 ){
            $tipo =5;
        }  
        if($request->pageSize == null){ 
            $request->pageSize = 10;
        }

        $token = json_decode($resp->getBody());
        $bearer = $token->access_token;

        if($tipo == 5){
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearer
            ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/lista_atendimentos?Acesso='.$uuid.'&Tipo='.$tipo.'&DataInicial='.$startdate.'&DataFinal='.$finaldate. '&PageNo='.$request->pageNo .'&Order='.$request->Order.'&PageSize='.$request->pageSize . '&NomePaciente='.$request->NomePaciente .'&FiltroProcedencia=' . $request->FiltroProcedencia);

        }else{
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearer
            ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/lista_atendimentos?Acesso='.$uuid.'&Tipo='.$tipo.'&DataInicial='.$startdate.'&DataFinal='.$finaldate. '&PageNo='.$request->pageNo .'&Order='.$request->Order.'&PageSize='.$request->pageSize . '&NomePaciente='.$request->NomePaciente);
        }

        $hospital = Hospitais::where('uuid', $uuid)->first();
        
        //GERA LOG
        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 10;
        $saveLog->uuid_hospital_atendimento = $uuid;
        $saveLog->save();

        $return = json_decode($response->getBody());
        
        foreach ($return->Atendimentos as $item){
            if(empty($item->lista_atendimento)){  
                return response()->json(['status' => 'Hospital não encontrado!',
                        'message' => 'Não foram encontrados dados para essas datas'
                
                ],404);         
                
           }else{
            return $response;
           }
        }


    }

    public function listAttendanceDetails($uuid, $atendimento,  Request $request)
    {


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
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/atend_detalhe?Tipo='.$tipo.'&NumAtendimento='.$atendimento.'&Acesso='. $uuid);

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


    public function downloadExams($uuid, $atendimento, $r_id, $seqexame, Request $request)
    {



        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 8;
        $saveLog->numatendimento = $atendimento;
        $saveLog->uuid_hospital_atendimento = $uuid;
        $saveLog->save();

        return response()->json(['http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/laudo?Hash='. $r_id.'&SeqExame='.  $seqexame], 200);
    }

    public function createObservation(Request $request){

        $data = $request->only('numatendimento', 'observation');
        $user = Auth::user();

        

        try{
            \DB::beginTransaction();

                $observation = ObservationsAttedance::where('numatendimento', $data['numatendimento'])->get();


                if(!empty($observation)){
                    ObservationsAttedance::where('numatendimento', $data['numatendimento'])->delete();
                }

                $newObservation = new ObservationsAttedance();
                $newObservation->numatendimento = $data['numatendimento'];
                $newObservation->observation = $data['observation'];
                $newObservation->id_user = $user->id;
                $newObservation->user = $user->name;
                $newObservation->save();

                $log = Auth::user();
                $saveLog = new UserLog();
                $saveLog->id_user = $log->id;
                $saveLog->ip_user = $request->ip();
                $saveLog->id_log = 13 ;
                $saveLog->numatendimento = $data['numatendimento'];
                $saveLog->save();
                


            \DB::commit();
        } catch (\Throwable $th) {
               \DB::rollback();
                return ['message' => 'Não foi possível salvar no banco de dados', 400];
            }
            return response()->json(['status' => 'Sucesso!' , 'message' => $newObservation], 200);
    }

    public function getObservation(Request $request, $id){



        $observation = ObservationsAttedance::where('numatendimento', $id)->get();
        if(count($observation) == 0){
            return response()->json(['status' => 'Observação não existe' ], 404);
        }

        return response()->json(['status' => 'ok' , 'message' => $observation], 200);
    }

    public function addExameSolicitation(Request $request){

        $data = $request->only('numatendimento', 'solicitation');

        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 13 ;
        $saveLog->numatendimento = $data['numatendimento'];
        $saveLog->save();
        
        try {

            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->ip_user = $request->ip();
            $saveLog->id_log = 15 ;
            $saveLog->numatendimento = $data['numatendimento'];
            $saveLog->save();
            
            /* Enviar e-mail para o usuário com sua senha de acesso */
            Mail::to(['gian@mageda.digital', 'elson@mageda.digital'])->send(new SolicitationAddExam($data));
            return response()->json(['status' => 'Solicitação enviada'], 200);
        } catch (Exception $ex) {
            dd($ex);
            return response()->json(['message' => 'Não foi possível enviar', $ex], 500);
        }
    }

}
