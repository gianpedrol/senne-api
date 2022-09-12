<?php

namespace App\Http\Controllers;

use App\Mail\emailPendentRegister;
use App\Models\User;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Mail\emailRegisterPartner;
use App\Mail\emailSolicitationDoctor;
use App\Models\DomainHospital;
use App\Models\Hospitais;
use App\Models\UserPermissoes;
use App\Models\UsersGroup;
use App\Models\UsersHospitals;
use Exception;

/* 
Status 

0 - inativo
1 - ativo
2 - precisa alterar senha, para ativar
3 - pendente aprovação Senne

ROLE ID

1 - SENNE MASTER
2 - USER HOSPITAL
3 - PACIENTE
4 - MÉDICO PARTICULAR
*/

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/patient/register",
     * operationId="Register Patient",
     * tags={"Register Patient"},
     * summary="Register Patient",
     * description="Register Patient ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="cpf", type="text"),
     *               @OA\Property(property="cnpj", type="text"),
     *               @OA\Property(property="phone", type="text")
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="User registered successfully!",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="User registered successfully!",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function registerPatient(Request $request)
    {
        $data = $request->only(['name', 'cpf', 'phone', 'email', 'policy']);

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' =>"User already exists!"], 400);
        }

        try {
            \DB::beginTransaction();

            //Define nivel user Senne
            $role_id = 3;

            //$senha_md5= Str::random(8);//Descomentar após testes
            $senha_md5 = '654321';
            $senha_temp = bcrypt($senha_md5);

            $newUser = new User();
            $newUser->name = $data['name'];
            $newUser->email = $data['email'];
            $newUser->cpf = $data['cpf'];
            $newUser->phone = $data['phone'];
            $newUser->policy = $data['policy'];
            $newUser->status = 1;
          //  $newUser->cod_pf = 'E2C2F72E90ED4552E053E600A8C0FE22'; apenas para testes realizados com uuid enviado pela Senne
            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            $newUser->save();


            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }

        $status = Password::sendResetLink(
            $request->only('email'),
        );

        if ($status == Password::RESET_LINK_SENT) {
            return [
                'status' => __($status),
                'message' => "User registered successfully!", 'data' => $newUser
            ];
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    /**
     * 
     * 
     * @OA\Post(
     * path="/api/doctor/register",
     * operationId="Register Doctor",
     * tags={"Register Doctor"},
     * summary="Register Doctor",
     * description="Register Doctor ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="cpf", type="text"),
     *                @OA\Property(property="crm", type="text"),
     *               @OA\Property(property="phone", type="text"),
     *               @OA\Property(property="especialidade", type="text"),
     *               @OA\Property(property="novidades", type="boolean")             
     *               
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="User registered successfully!",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="User registered successfully!",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function registerDoctor(Request $request)
    {


        $data = $request->only(['name', 'crm','cpf', 'phone', 'email', 'especialidade', 'novidades', 'policy']);

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' =>"User already exists!"], 400);
        }

        try {
            \DB::beginTransaction();

            //Define nivel user Senne
            $role_id = 4;

            //$senha_md5= Str::random(8);//Descomentar após testes
            $senha_md5 = '654321';
            $senha_temp = bcrypt($senha_md5);

            $newUser = new User();
            $newUser->name = $data['name'];
            $newUser->email = $data['email'];;
            $newUser->cpf = $data['cpf'];
            $newUser->crm = $data['crm'];
            $newUser->phone = $data['phone'];
            $newUser->especialidade = $data['especialidade'];
            if(!empty($data['novidades'])){
                $newUser->news_email = $data['novidades'];
            }
            $newUser->status = 3;
            $newUser->policy = $data['policy'];
            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            $newUser->save();


            \DB::commit();

            try {
                /* Enviar e-mail para o usuário com sua senha de acesso */
                Mail::to($newUser->email)->send(new emailPendentRegister($data));
                /* Enviar e-mail para Senne aprovar médico na plataforma */
                Mail::to(['gian@mageda.digital', 'elson@mageda.digital', 'gustavo@mageda.digital', 'ti@senneliquor.com.br'])->send(new emailSolicitationDoctor($data));
                return response()->json(['status' => 'solicitation sended', $newUser], 200);
            } catch (Exception $ex) {
                dd($ex);
                return response()->json(['error' => 'cannot be sended', $ex], 500);
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }
    }

    /**
     * @OA\Get(
     *   tags={"List Speciality "},
     *   path="/api/doctor/speciality",
     *   summary="Summary",
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function getSpeciality(Request $request)
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
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearer
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/especialidade');

        $medical_specility = json_decode($response->getBody());
        //dd($medical_specility);
        return response()->json([$medical_specility]);
    }

    /**
     * @OA\Get(
     *   tags={"List Hospitals "},
     *   path="/api/hospital/list/{id}",
     *   summary="Summary",
     *      @OA\Parameter(
     *      name="id",
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
    public function getHospitalId(Request $request, $id)
    {

        $hospitals = DomainHospital::from('domains_hospitals as domain')
            ->select('hos.name', 'domain.domains',)
            ->join('hospitais as hos', 'domain.codprocedencia', '=', 'hos.codprocedencia')
            ->where('hos.id', '=', $id)
            ->get();

        if (empty($hospitals)) {
            $hospital = Hospitais::where('id', $id)->first();

            return response()->json($hospital);
        }

        return response()->json($hospitals);
    }

    /**
     * @OA\Get(
     *   tags={"List Hospitals "},
     *   path="/api/hospital/list",
     *   summary="Summary",
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function getHospital()
    {

        $hospitals = DB::table('hospitais')->orderBy('name')->get();

        return response()->json($hospitals);
    }
    /**
     * @OA\Post(
     * path="/api/partner/register",
     * operationId="Register Partner",
     * tags={"Register Partner"},
     * summary="Register Partner",
     * description="Register Partner ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="cpf", type="text"),
     *               @OA\Property(property="phone", type="text"),         
     *               @OA\Property(property="nameempresa", type="text"),         
     *               @OA\Property(property="razaosocial", type="text"),         
     *               @OA\Property(property="cnpj", type="text"),         
     *               @OA\Property(property="classification", type="text"),         
     *               @OA\Property(property="uf", type="text"),         
     *               @OA\Property(property="cep", type="text"),         
     *               @OA\Property(property="city", type="text"),         
     *               @OA\Property(property="addres", type="text")       
     *               
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="solicitation sended",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="solicitation sended",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     ***/
    public function registerPartner(Request $request)
    {
        $data = $request->only(['name', 'cpf', 'phone', 'email', 'nameempresa', 'razaosocial', 'cnpj', 'classification', "uf", "cep", "city", "address", "number", 'policy']);

        $usersMasters = User::where('role_id', 1)->get();
        $sendTo = [];

        foreach ($usersMasters as $user) {
            $sendTo = [
                'email' => $user->email
            ];
        }
        try {
            /* Enviar e-mail para o usuário com sua senha de acesso */
            Mail::to(['gian@mageda.digital', 'elson@mageda.digital', 'gustavo@mageda.digital', 'ti@senneliquor.com.br'])->send(new emailRegisterPartner($data));
            return response()->json(['status' => 'solicitation sended'], 200);
        } catch (Exception $ex) {
            dd($ex);
            return response()->json(['error' => 'cannot be sended', $ex], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/user/hospital/register",
     * operationId="Register User Hospital",
     * tags={"Register User Hospital"},
     * summary="Register User Hospital",
     * description="Register User Hospital ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *            @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="cpf", type="text"),
     *               @OA\Property(property="phone", type="text"),
     *               @OA\Property(property="department", type="text"),        
     *               @OA\Property(
     *                 property="hospitals",
     *                 type="array",
     *                 @OA\Items()
     *               ), 
     *               @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items()
     *               ),      
     *            ),
     *           )
     *        ),
     *      @OA\Response(
     *          response=201,
     *          description="User registered successfully!",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="User registered successfully!",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function RegisterUserHospital(Request $request)
    {


        $data = $request->only(['name', 'cpf', 'phone', 'email', 'department', 'policy']);
        $permissions = $request->permissions;
        $hospitalsId = $request->hospitals;

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' =>"User already exists!"], 400);
        }


        /* CHECAR SE EMAIL CONFERE COM DOMINIO */
        $userEmail = $data['email'];
        $dominio = explode('@', $userEmail);
        //dd($dominio[1]);
        $domainEmail = $dominio[1];


        $hospital = Hospitais::where('id', $hospitalsId)->first();
        $hospitalsDomain = DomainHospital::from('domains_hospitals as domain')
        ->select('hos.name', 'domain.domains')
        ->join('hospitais as hos', 'hos.codprocedencia', '=', 'domain.codprocedencia')
        ->where('hos.id', '=', $hospital->id)
        ->get()
        ->toArray();

        if(!empty($hospitalsDomain)){
            foreach($hospitalsDomain as $item){
                if($item['domains'] != $domainEmail ){
                    return response()->json(['error' => 'Seu e-mail é diferente do email do hospital'], 404);
                }else{
                    $hospitalsCheck = true;
                }
            }
        }else{
            $hospitalsCheck = true;
        }

        
 /*        $hospitals = DomainHospital::from('domains_hospitals as domain')
        ->select('hos.name', 'domain.domains')
        ->join('hospitais as hos', 'hos.codprocedencia', '=', 'domain.codprocedencia')
        ->where('hos.id', '=', $hospitalsId)
        ->where('domain.domains','=', $domainEmail)
        ->get()
        ->toArray();
        
        
        $domain = [];

        foreach ($hospitals as $hospital) {
            $domain = [
                'email' => $hospital['domains']
            ];
        }
        || empty($domain) == true
        */

        if ($hospitalsCheck == true) {
            try {
                \DB::beginTransaction();

                //Define nivel user Senne
                $role_id = 2;

                //$senha_md5= Str::random(8);//Descomentar após testes
                $senha_md5 = '654321';
                $senha_temp = bcrypt($senha_md5);

                $newUser = new User();
                $newUser->name = $data['name'];
                $newUser->email = $data['email'];
                $newUser->cpf = $data['cpf'];
                $newUser->phone = $data['phone'];
                $newUser->policy = $data['policy'];
                $newUser->status = 3;
                $newUser->role_id = $role_id;
                $newUser->password = $senha_temp;
                $newUser->save();


                /* if (!empty($hospitals)) {
                    UsersHospitals::create(['id_hospital' =>  $hospitalsId, 'id_user' => $newUser->id]);
                }*/

                /* Salva mais de um hospital ao usuário*/
                if (!empty($hospitalsId)) {
                    foreach ($hospitalsId  as $id_hospital) {
                        UsersHospitals::create(['id_hospital' =>  $id_hospital, 'id_user' => $newUser->id]);
                    }
                }

                /* Salva mais de um hospital ao usuário*/
                /*  if (!empty($hospitals)) {
               dd($hospitals);
               $info_hospital = Hospitais::where('id', $hospitals[0])->first();
               UsersGroup::create(['id_group' => $info_hospital->grupo_id, 'id_user' => $newUser->id]);
           }*/

                /* Salva permissões do Usuário */
                if (!empty($permissions)) {
                    foreach ($permissions as $id_permission) {
                        UserPermissoes::create(['id_permissao' => $id_permission, 'id_user' => $newUser->id]);
                    }
                }

                \DB::commit();
                try {
                    /* Enviar e-mail para o usuário com sua senha de acesso */
                    Mail::to($newUser->email)->send(new emailPendentRegister($data));
                    return response()->json(['status' => 'solicitation sended', $newUser], 200);
                } catch (Exception $ex) {
                    dd($ex);
                    return response()->json(['error' => 'cannot be sended', $ex], 500);
                }
            } catch (\Throwable $th) {
                dd($th->getMessage());
                \DB::rollback();
                return ['error' => 'Could not write data', 400];
            }

            return response()->json([
                'message' => "User registered successfully!", 'data' => $newUser
            ], 200);
        } else {
            return response()->json(['error' => 'Domain is invalid for this hospital'], 400);
        }
    }
    /**
     * @OA\Get(
     *   tags={"List Hospitals "},
     *   path="api/hospitals/domain/list",
     *   summary="Summary",
     *        @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="email", type="email"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function getHospitalDomain(Request $request)
    {
        $domain = $request->domain;
        $hospitals = DomainHospital::from('domains_hospitals as domain')
            ->select('hos.name', 'domain.domains',)
            ->join('hospitais as hos', 'domain.codprocedencia', '=', 'hos.codprocedencia')
            ->where('domain.domains', '=', $domain)
            ->get();
        return response()->json($hospitals);
    }

    public function saveHospitalDomain(Request $request)
    {
        $hospitals = Hospitais::where('id', $request->id)->update(['domains' => $request->domain]);

        dd($hospitals);
    }
}
