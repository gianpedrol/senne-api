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

    public function registerPatient(Request $request)
    {
        $data = $request->only(['name', 'cpf', 'phone','ramal', 'celphone', 'email', 'policy']);

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['message' =>"Usuário já cadastrado!"], 400);
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
            if(!empty($data['phone'])){
                $newUser->phone = $data['phone'];
             }
             if(!empty($data['ramal'])){
                $newUser->ramal = $data['ramal'];
             }
             if(!empty($data['celphone'])){
                $user->cellphone = $data['celphone'];
            }
            $newUser->policy = $data['policy'];
            $newUser->status = 3;
          //  $newUser->cod_pf = 'E2C2F72E90ED4552E053E600A8C0FE22'; 
            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            $newUser->save();


            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['message' => 'Não foi possível salvar no banco de dados', 400];
        }

        $status = Password::sendResetLink(
            $request->only('email'),
        );

        if ($status == Password::RESET_LINK_SENT) {
            return [
                'status' => __($status),
                'message' => "Usuário registrado com sucesso!", 'data' => $newUser
            ];
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }


    public function registerDoctor(Request $request)
    {


        $data = $request->only(['name', 'crm','cpf', 'phone','ramal','celphone', 'email', 'especialidade', 'novidades', 'policy']);

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['message' =>"Usuário já existe!"], 400);
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
            if(!empty($data['phone'])){
                $newUser->phone = $data['phone'];
             }
             if(!empty($data['ramal'])){
                $newUser->ramal = $data['ramal'];
             }
             if(!empty($data['celphone'])){
                $user->cellphone = $data['celphone'];
            }
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
                return response()->json(['status' => 'Solicitação enviada', $newUser], 200);
            } catch (Exception $ex) {
                dd($ex);
                return response()->json(['message' => 'Não foi possível enviar', $ex], 500);
            }
        } catch (\Throwable $th) {
          //  dd($th->getMessage());
            \DB::rollback();
            return ['message' => 'Não foi possivel salvar no banco de dados', 'erro' => $th->getMessage(), 400];
        }
    }

  
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
       
        return response()->json([$medical_specility]);
    }

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

  
    public function getHospital()
    {

        $hospitals = DB::table('hospitais')->orderBy('name')->get();

        return response()->json($hospitals);
    }
    
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
            Mail::to(['gian@mageda.digital', 'elson@mageda.digital','ti@senneliquor.com.br'])->send(new emailRegisterPartner($data));
            return response()->json(['status' => 'Solicitação enviada'], 200);
        } catch (Exception $ex) {
           // dd($ex);
            return response()->json(['message' => 'Não foi possível enviar', $ex], 500);
        }
    }

  
    public function RegisterUserHospital(Request $request)
    {


        $data = $request->only(['name', 'cpf', 'phone', 'ramal','celphone','email', 'department', 'policy']);
        $permissions = $request->permissions;
        $hospitalsId = $request->hospitals;
        $name = $data['name'];

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['message' =>"Usuário já cadastrado!"], 400);
        }


        /* CHECAR SE EMAIL CONFERE COM DOMINIO */
        $userEmail = $data['email'];
        $dominio = explode('@', $userEmail);
        
        $domainEmail = $dominio[1];


        $hospital = Hospitais::where('id', $hospitalsId)->first();
        $hospital['Domain'] = DomainHospital::from('domains_hospitals as domain')
        ->select('hos.name', 'domain.domains')
        ->join('hospitais as hos', 'hos.codprocedencia', '=', 'domain.codprocedencia')
        ->where('hos.id', '=', $hospital->id)
        ->get()
        ->toArray();

        if(!empty($hospital['Domain'])){
            
            $item = DomainHospital::from('domains_hospitals as domain')
            ->select('hos.name', 'domain.domains')
            ->join('hospitais as hos', 'hos.codprocedencia', '=', 'domain.codprocedencia')
            ->where('hos.id', '=', $hospital->id)
            ->where('domain.domains', '=', $domainEmail )
            ->get()
            ->toArray();

        $hospital_Check = false; 

    /*        if(empty($item) ){
                   $hospital_Check = false;                 
                   return response()->json(['message' => 'Seu e-mail é diferente do email do hospital'], 404);
               }else {
                   $hospital_Check = true; 
               }  */
               
        }
    
        if(empty($hospitalsDomain)){
            $hospital_Check = true;
        }

        if ($hospital_Check === true)  {
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
                if(!empty($data['phone'])){
                    $newUser->phone = $data['phone'];
                 }
                 if(!empty($data['ramal'])){
                    $newUser->ramal = $data['ramal'];
                 }
                 if(!empty($data['celphone'])){
                    $user->cellphone = $data['celphone'];
                }
                $newUser->policy = $data['policy'];
                $newUser->status = 3;
                $newUser->role_id = $role_id;
                $newUser->password = $senha_temp;
                $newUser->save();


                /* Salva mais de um hospital ao usuário*/
                if (!empty($hospitalsId)) {
                    foreach ($hospitalsId  as $id_hospital) {
                        UsersHospitals::create(['id_hospital' =>  $id_hospital, 'id_user' => $newUser->id]);
                    }
                }

 
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
                    return response()->json(['status' => 'Solicitação enviada', $newUser], 200);
                } catch (Exception $ex) {
                    dd($ex);
                    return response()->json(['message' => 'Não foi póssível enviar a solicitação', $ex], 500);
                }
            } catch (\Throwable $th) {

                \DB::rollback();
                return ['message' => 'Não foi possivel salvar no banco de dados', 'erro' => $th->getMessage(), 400];
            }

            return response()->json([
                'message' => "Usuário registrado com sucesso!", 'data' => $newUser
            ], 200);
        } else {
            return response()->json(['message' => 'O dominio é inválido para este hospital'], 400);
        }
    }
 
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
