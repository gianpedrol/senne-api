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
        $response = Http::post('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/resultado', [
            'NumeroCPF' => $request->cpf,
            'DataNascimento' => $request->datanascimento
        ]);

        $items = json_decode($response->getBody());

        return $items;
    }

    /**
     * @OA\Get(
     *   tags={"List Attedance"},
     *   path="/api/treatment/exams/{uuid}/{numatendimento}",
     *   summary="Summary",
     *      @OA\Parameter(
     *      name="uuid",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="numatendimento",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),    
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function listAttendance($uuid, $atendimento,  Request $request)
    {

        $client = 'mUlsPn8LSRPaYu1zJkbf2w..';
        $client_secret = 'U8fQdDraw7r7Yq74mpQ0IA..';
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
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/atendimento/' . $uuid . '/' . $atendimento);

        /* CONSULTA API DE SISTEMA DA SENNE */
        //$response = Http::get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento/' . $uuid . '/' . $atendimento);

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

    /**
     * @OA\Get(
     *   tags={"List Attedance Hospital - Date"},
     *   path="/api/treatment/exams/{uuid}/{startdate}/{finaldate}",
     *   summary="Summary",
     *      @OA\Parameter(
     *      name="uuid",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="startdate",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="finaldate",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),       
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function listAttendanceDate($uuid, $startdate, $finaldate, Request $request)
    {

        $client = 'mUlsPn8LSRPaYu1zJkbf2w..';
        $client_secret = 'U8fQdDraw7r7Yq74mpQ0IA..';
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
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/proced_atendimentos/' . $uuid . '/' . $startdate . '/' . $finaldate);


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
    /**
     * @OA\Get(
     *   tags={"List Attedance Details"},
     *   path="/api/treatment/details/{uuid}/{atendimento}",
     *   summary="Summary",
     *      @OA\Parameter(
     *      name="uuid",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="atendimento",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),     
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function listAttendanceDetails($uuid, $atendimento,  Request $request)
    {

        $client = 'mUlsPn8LSRPaYu1zJkbf2w..';
        $client_secret = 'U8fQdDraw7r7Yq74mpQ0IA..';
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
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/atendimento_detalhe/' . $uuid . '/' . $atendimento);

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
    /**
     * @OA\Get(
     *   tags={"List Principal Report"},
     *   path="/api/treatment/report/{uuid}/{r_id}",
     *   summary="Summary",
     *      @OA\Parameter(
     *      name="uuid",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="r_id",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),       
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
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
}
