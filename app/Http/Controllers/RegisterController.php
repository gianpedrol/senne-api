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
use App\Models\DomainHospital;
use App\Models\Hospitais;
use App\Models\UserPermissoes;
use App\Models\UsersGroup;
use App\Models\UsersHospitals;
use Exception;

class RegisterController extends Controller
{
    public function registerPatient(Request $request)
    {
        $data = $request->only(['name', 'cpf', 'phone', 'email']);

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
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
            $newUser->status = 2;
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

    public function registerDoctor(Request $request)
    {


        $data = $request->only(['name', 'crm', 'phone', 'email', 'especialidade', 'novidades']);

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
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
            $newUser->email = $data['email'];
            $newUser->crm = $data['crm'];
            $newUser->phone = $data['phone'];
            $newUser->especialidade = $data['especialidade'];
            $newUser->news_email = $data['novidades'];
            $newUser->status = 2;

            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            $newUser->save();


            \DB::commit();

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
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }
    }

    public function getSpeciality(Request $request)
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
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/especialidade');

        $medical_specility = json_decode($response->getBody());
        //dd($medical_specility);
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

        $hospitals = Hospitais::all();
        return response()->json($hospitals);
    }

    public function registerPartner(Request $request)
    {
        $data = $request->only(['name', 'cpf', 'phone', 'email', 'nameempresa', 'razaosocial', 'cnpj', 'classification', "uf", "cep", "city", "address", "number"]);

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

    public function RegisterUserHospital(Request $request)
    {


        $data = $request->only(['name', 'cpf', 'phone', 'email', 'department']);
        $permissions = $request->permissions;
        $hospitals = $request->hospitals;

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
        }

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
            $newUser->id_department = $data['department'];
            $newUser->status = 2;
            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            $newUser->save();

            /* Salva mais de um hospital ao usuário*/
            if (!empty($hospitals)) {
                foreach ($hospitals as $id_hospital) {
                    UsersHospitals::create(['id_hospital' => $id_hospital, 'id_user' => $newUser->id]);
                }
            }

            /* Salva mais de um hospital ao usuário*/
            if (!empty($hospitals)) {

                $info_hospital = Hospitais::where('id', $hospitals[0])->first();
                UsersGroup::create(['id_group' => $info_hospital->grupo_id, 'id_user' => $newUser->id]);
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

        /*   $status = Password::sendResetLink(
            $request->only('email'),
        );

        if ($status == Password::RESET_LINK_SENT) {
            return [
                'status' => __($status)
            ];
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);*/

        return response()->json([
            'message' => "User registered successfully!", 'data' => $newUser
        ], 200);
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
