<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Hospitais;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\UserLog;
use App\Models\UserPermissoes;
use App\Models\UsersGroup;
use App\Models\UsersHospitals;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function login(Request $request, $role_id)
    {
        $user = User::where('email', $request->email)->first();

        /* if ($role_id != $user->role_id) {

        }*/

        if ($user->status == 0) {
            return response()->json([
                'message'   => 'The user is inativated',
            ], 404);
        }

        if ($user->status == 2) {

            $status = Password::sendResetLink(
                $request->only('email'),
            );

            if ($status == Password::RESET_LINK_SENT) {

                return response()->json([
                    'status' => __($status),
                    'message'   => 'The user is not activated, check your email'
                ], 400);
            }

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        if ($user->status == 3) {
            return response()->json([
                'message'   => 'User is awaiting approval',
            ], 404);
        }

        if ($user->role_id == 1 || $role_id == $user->role_id) {
            $array = ['message' => ''];
            $creds = $request->only('email', 'password');
            $token = Auth::attempt($creds);



            if ($token) {
                $user['email'] = $creds;
                $array['token'] = $token;
            } else {
                $array['message'] = 'Incorrect username or password';
            }

            $user = User::where('email', $user['email'])->first();
            $log = User::where('email', $user['email'])->first();

            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->ip_user = $request->ip();
            $saveLog->id_log = 1;
            $saveLog->save();

            $user = [];
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message'   => 'The user can t be found',
                ], 404);
            } else {

                $user['hospitals'] = UsersHospitals::from('users_hospitals as userhos')
                    ->select('hos.id', 'hos.grupo_id', 'hos.name as name',  'hos.uuid')
                    ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                    ->where('id_user', $user->id)
                    ->get();


                $user['permissoes'] = UserPermissoes::where('id_user', $user->id)->select('id_permissao as id')->get();
                return response()->json(['message' => "User Logged in!", 'token' => $array['token'], 'user' => $user], 200);
            }
        } else {
            return response()->json([
                'message'   => 'The user cannot login at this route',
            ], 500);
        }
    }



    public function create(Request $request)
    {

        /*
            Método responsável por gravar os usuários da Senne, esses uauários possui permissão role_id 1
            ele tem acesso a todo o conteudo do sistema
        */

        $data = $request->only(['name', 'cpf', 'email', 'cnpj', 'phone']);

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
        }


        try {
            \DB::beginTransaction();

            //Define nivel user Senne
            $role_id = 1;

            //$senha_md5= Str::random(8);//Descomentar após testes
            $senha_md5 = '%&yAXNF';
            $senha_temp = bcrypt($senha_md5);

            $newUser = new User();
            $newUser->name = $data['name'];
            $newUser->email = $data['email'];
            $newUser->cpf = $data['cpf'];
            $newUser->cnpj = $data['cnpj'];
            $newUser->phone = $data['phone'];
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


        return response()->json(['message' => "User registered successfully!", 'data' => $newUser], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {


        $log = Auth::user();
        $saveLog = new UserLog();
        $saveLog->id_user = $log->id;
        $saveLog->ip_user = $request->ip();
        $saveLog->id_log = 2;
        $saveLog->save();

        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    public function unauthorized()
    {
        return response()->json(['error' => "Unauthorized user!"], 401);
    }
}
