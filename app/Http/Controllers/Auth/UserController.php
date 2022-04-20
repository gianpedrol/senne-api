<?php

namespace App\Http\Controllers\Auth;

use App\Jobs\sendEmailPasswordReset;
use App\Jobs\sendEmailVerification;
use App\Mail\emailPasswordReset;
use App\Mail\emailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use League\Flysystem\Exception;

use App\Http\Controllers\Controller;
use App\Models\Hospitais;
use App\Models\UserLog;
use App\Models\UserPermissoes;
use App\Models\UsersHospitals;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Auth\DB;

class UserController extends Controller
{
    public function createUserMaster(Request $request)
    {

        $data = $request->only(['name', 'cpf', 'email', 'id_hospital', 'unidade', 'permissao']);
        $user = User::where('email', $data['email'])->first();
        $permissions = $request->only('permissions');
        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
        }

        try {
            \DB::beginTransaction();

            //Define nivel user Senne
            $role_id = 1;

            //$senha_md5= Str::random(8);//Descomentar após testes
            $senha_md5 = '654321';
            $senha_temp = bcrypt($senha_md5);

            $newUser = new User();
            $newUser->name = $data['name'];
            $newUser->email = $data['email'];
            $newUser->cpf = $data['cpf'];
            $newUser->telefone = $data['telefone'];
            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            $newUser->save();

            $userHospital = new UsersHospitals();
            $userHospital->id_user = $newUser->id;
            $userHospital->id_hospital = $data['id_hospital'];
            $userHospital->save();

            foreach ($permissions as $permission) {

                $dataPermission = [
                    'id' => $permission['id']
                ];
            }

            //PERMISSOES
            $userPermissao = new UserPermissoes();
            $userPermissao->id_user = $user->id;
            $userPermissao->id_permissao = $dataPermission['id'];
            $userPermissao->save();

            $userPermissao = new UserPermissoes();
            $userPermissao->id_user = $newUser->id;
            $userPermissao->id_hospital =  $data['id_hospital'];
            $userPermissao->id_permissao = $data['permissao'];
            $userPermissao->save();

            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->ip_user = $request->ip();
            $saveLog->id_log = 4;
            $saveLog->save();




            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }
        return response()->json(['message' => "User registered successfully!", 'data' => $newUser], 200);
    }
    public function createUser(Request $request)
    {

        $data = $request->only(['name', 'cpf', 'phone', 'email']);
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
            $newUser->role_id = $role_id;
            $newUser->password = $senha_temp;
            $newUser->save();

            /* Salva mais de um hospital ao usuário*/
            if (!empty($hospitals)) {
                foreach ($hospitals as $id_hospital) {
                    UsersHospitals::create(['id_hospital' => $id_hospital, 'id_user' => $newUser->id]);
                }
            }


            /* Salva permissões do Usuário */
            if (!empty($permissions)) {
                foreach ($permissions as $id_permission) {
                    UserPermissoes::create(['id_permissao' => $id_permission, 'id_user' => $newUser->id]);
                }
            }



            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->ip_user = $request->ip();
            $saveLog->id_log = 4;
            $saveLog->save();




            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }
        return response()->json(['message' => "User registered successfully!", 'data' => $newUser], 200);
    }
    public function update(Request $request)
    {

        $id = $request->id;
        $data = $request->only('name', 'phone', 'cpf', 'email');
        $permissions = $request->permissions;
        $hospitals = $request->hospitals;

        //Validar se email existe!


        try {
            \DB::beginTransaction();
            //atualizando o HOSPITAL
            $user = User::where('id', $id)->first();
            if ($user) {
                $user->update($data);
            }


            /* Salva mais de um hospital ao usuário*/
            UsersHospitals::where('id_user', $user->id)->delete(); //Deleta os registros
            if (!empty($hospitals)) {
                foreach ($hospitals as $id_hospital) {
                    UsersHospitals::create(['id_hospital' => $id_hospital, 'id_user' => $user->id]);
                }
            }


            /* Salva permissões do Usuário */
            UserPermissoes::where('id_user', $user->id)->delete(); //Deleta os registros
            if (!empty($permissions)) {
                foreach ($permissions as $id_permission) {
                    UserPermissoes::create(['id_permissao' => $id_permission, 'id_user' => $user->id]);
                }
            }


            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->ip_user = $request->ip();
            $saveLog->id_log = 3;
            $saveLog->save();

            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }






        return response()->json(['message' => 'user updated']);
    }

    public function delete(Request $request)
    {
        $id = $request->id;

        try {
            $user = User::findOrFail($id)->delete();

            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->ip_user = $request->ip();
            $saveLog->id_log = 5;
            $saveLog->save();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Fail on delete a user'], 400);
        }
    }

    public function sendResetPassword(Request $request)
    {

        $frontUrl = env('FRONTEND_URL');
        $frontRoute = env('FRONTEND_RESET_PASSWORD_URL');

        $email = $request->get('email');
        $user = User::where('email', $email)->get();


        if (count($user) > 0) {
            $urlTemp = $frontUrl . $frontRoute . URL::temporarySignedRoute(
                'verifyResetRoute',
                now()->addMinutes(30),
                ['user' => $user[0]['id']]
            );

            sendEmailPasswordReset::dispatch($user[0], $urlTemp);

            return response()->json(['message' => 'email reset password send']);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function verifyResetRoute(Request $request)
    {

        if (!$request->hasValidSignature()) {
            abort(401);
        }

        return response()->json(['message' => 'valid url']);
    }

    public function reset(Request $request)
    {
        $id = $request->id;
        $password = Hash::make($request->get('password'));

        try {
            User::findOrFail($id)->update(['password' => $password]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Fail to reset password'], 400);
        }

        return response()->json(['message' => 'Password reset successful']);
    }

    public function verification(Request $request)
    {
        $user_id = $request->route('user');

        if (!$request->hasValidSignature()) {
            abort(401);
        }

        try {
            $user = User::find($user_id);

            $user->markEmailAsVerified();

            return response()->json(['message' => 'verified user email']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'erro on try to validade user'], 400);
        }
    }

    public function resend(Request $request)
    {
        $id = $request->get('id');

        $frontUrl = env('FRONTEND_URL');
        $frontRoute = env('FRONTEND_EMAIL_VERIFY_URL');

        $user = User::find($id);

        if ($user) {
            $urlTemp = $frontUrl . $frontRoute . URL::temporarySignedRoute(
                'verification',
                now()->addMinutes(30),
                ['user' => $user->id]
            );

            sendEmailVerification::dispatch($user, $urlTemp);
        } else {
            response()->json(['message' => 'User not found'], 404);
        }
    }

    public function logsUser(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return response()->json([
                'message'   => 'The User can t be found',
            ], 404);
        } else {

            $user['logs'] = UserLog::from('logs_user as log')
                ->select('log.id_log', 'act.log_description as log_description', 'log.ip_user', 'log.created_at as time_action')
                ->join('logs_action as act', 'act.id', '=', 'log.id_log')
                ->where('id_user', $user->id)
                ->get();
            return response()->json(
                ['status' => 'success', 'User' => $user],
                200
            );
        }
    }

    public function listAllUser()
    {

        //Trazemos os usuarios que possui vinculo com hospitais
        $data = User::from('users as user')
            ->select('user.id', 'user.name', 'user.email')
            ->where('user.role_id', '!=', 1)
            ->get()
            ->toArray();

        $users = User::where('role_id', '!=', 1)->get();

        // Trazemos usuarios que não possui vinculo com hospitais
        /*  $user_db = [];
        foreach ($users as $key => $user) {
            // dd($user);
            $user_nothos = UsersHospitals::where('id_user', $user->id)->first();

            if (empty($user_nothos) || empty($userlog)) {
                $user_db[$key]['id'] = $user->id;
                $user_db[$key]['name'] = $user->name;
                $user_db[$key]['email'] = $user->email;
            }
        }*/


        // Juntamos os usuários em uma só array
        $all_users = array_merge($data);


        //Rodamos o loop para trazer o ultimo log de cada usuário
        $retorno = [];
        foreach ($all_users as $key1 => $user_only) {
            $user_only['dateLogin'] = UserLog::where('id_user', $user_only['id'])->orderBy('id', 'DESC')->first('created_at');
            // $user_only['hospitais'] = UsersHospitals::where('id_user', $user_only['id'])->get();
            $user_only['hospitais'] = UsersHospitals::from('users_hospitals as userhos')
                ->select('hos.id as id_hospital', 'hos.name as name')
                ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                ->where('id_user', $user_only['id'])
                ->get();
            $retorno[] = $user_only;
        }

        return response()->json(
            ['status' => 'success', 'Users' => $retorno],
            200
        );
    }

    public function showUser(Request $request)
    {

        $user = [];
        $user = User::findOrFail($request->id);

        if (!$user) {
            return response()->json([
                'message'   => 'The user can t be found',
            ], 404);
        } else {


            $user['hospitals'] = UsersHospitals::from('users_hospitals as userhos')
                ->select('hos.name as name')
                ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                ->where('id_user', $user->id)
                ->get();


            $user['permissoes'] = UserPermissoes::where('id_user', $user->id)->select('id_permissao as id')->get();

            return response()->json(
                ['status' => 'success',  'users' => $user],
                200
            );
        }
    }
}
