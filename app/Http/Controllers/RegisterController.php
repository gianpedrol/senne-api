<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

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
       

        $data = $request->only(['name', 'crm', 'phone', 'email', 'especialidade']);

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

    public function getSpeciality(Request $request){
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
}
