<?php

namespace App\Http\Controllers;

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
use App\Models\DomainHospital;
use App\Models\Hospitais;
use App\Models\UserLog;
use App\Models\UserPermissoes;
use App\Models\UsersHospitals;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
//use App\Http\Controllers\Auth\DB;
use App\Models\Groups;
use App\Models\LogsExames;
use App\Models\UsersGroup;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Password;
use PHPUnit\TextUI\XmlConfiguration\Group;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use DB;

class UserController extends Controller
{

    public function createUser(Request $request)
    {

        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized"], 401);
            }
        }

        $data = $request->only(['name', 'cpf', 'phone', 'email', 'crm']);
        $permissions = $request->permissions;
        $hospitalsId = $request->hospitals;

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' => "User already exists!"], 200);
        }

        /* CHECAR SE EMAIL CONFERE COM DOMINIO */
        $userEmail = $data['email'];
        $dominio = explode('@', $userEmail);
        //dd($dominio[1]);
        $domainEmail = $dominio[1];



        $hospital = Hospitais::where('id', $hospitalsId)->first();

        $hospitals = DomainHospital::from('domains_hospitals as domain')
            ->select('hos.name', 'domain.domains',)
            ->join('hospitais as hos', 'hos.codprocedencia', '=', 'domain.codprocedencia')
            ->where('hos.id', '=', $hospitalsId)
            ->get()
            ->toArray();

        $domains = DomainHospital::from('domains_hospitals as domain')
            ->select('domain.domains')
            ->join('hospitais as hos', 'hos.codprocedencia', '=', 'domain.codprocedencia')
            ->where('hos.id', '=',  $hospitalsId)
            ->get();

        $domain = [];
        foreach ($hospitals as $hospital) {
            $domain = [
                'email' => $hospital['domains']
            ];
        }

        if (empty($domains) || $domainEmail === $domain['email']) {
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
                $newUser->crm = $data['crm'];
                $newUser->status = 2;
                $newUser->role_id = $role_id;
                $newUser->password = $senha_temp;
                $newUser->save();


                if (!empty($hospitals)) {
                    UsersHospitals::create(['id_hospital' =>  $hospitalsId, 'id_user' => $newUser->id]);
                }

                /* Salva mais de um hospital ao usuário*/
                /* if (!empty($hospitals)) {
                    foreach ($hospitals as $id_hospital) {
                    UsersHospitals::create(['id_hospital' =>  $id_hospital, 'id_user' => $newUser->id]);                        
                    }
                }*/

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



                //GERA LOG
                $log = Auth::user();
                $saveLog = new UserLog();
                $saveLog->id_user = $log->id;
                $saveLog->ip_user = $request->ip();
                $saveLog->id_log = 4;
                $saveLog->save();

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
        } else {
            return response()->json(['error' => 'Domain is invalid for this hospital'], 400);
        }
    }

    public function update(Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized 1"], 401);
            }
        }
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
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized 1"], 401);
            }
        }
        $id = $request->id;

        try {
            $user = User::findOrFail($id)->delete();
            return response()->json(['message' => 'user successfully deleted'], 200);
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

    public function inactivateUser($id, Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized 1"], 401);
            }
        }
        $id = $request->id;
        $status = $request->only('status');
        //dd($status['status']);
        try {
            $user = User::where('id', $id)->first();
            if ($user) {
                User::where('id', $id)->update(['status' => $status['status']]);
                //  $user->update(['status' => $status['status']]);
                return response()->json(['message' => 'user inactivated'], 200);
            } else {
                return response()->json(['error' => 'user cannot be inactivated'], 400);
            }

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

    public function logsUser(Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized 1"], 401);
            }
        }

        function datadate($data)
        {

            $data = str_replace("/", "-", $data);

            return date("Y-m-d", strtotime($data));
        }


        $data = $request->all();
        if (!empty($data['iniciodata'])) {
            $data['iniciodata'] = datadate($data['iniciodata']);
        }
        if (!empty($data['fimdata'])) {
            $data['fimdata'] = datadate($data['fimdata']);
        }

        $user = User::find($request->id);


        if (!$user) {
            return response()->json([
                'message'   => 'The User can t be found',
            ], 404);
        } else {


            $user['logs'] = UserLog::from('logs_user as log')
                ->select('log.id_log', 'act.log_description as log_description', 'log.created_at as timeAction', 'log.ip_user',  'log.numatendimento', 'hos.uuid', 'hos.name as hospitalName', 'group.name as groupName')
                ->join('logs_action as act', 'act.id', '=', 'log.id_log')
                ->leftJoin('hospitais as hos', 'hos.uuid', '=', 'log.uuid_hospital_atendimento')
                ->leftJoin('groups as group', 'group.id', '=', 'hos.grupo_id')
                ->where('id_user', $user->id)
                ->when(!empty($request->datainicio), function ($query) use ($data) {
                    return $query->whereDate('log.created_at', '>=', $data['datainicio']);
                })
                ->when(!empty($request->fimdata), function ($query) use ($data) {
                    return $query->whereDate('log.created_at', '>=', $data['fimdata']);
                })
                ->when(!empty($request->sort), function ($query) use ($data) {
                    return $query->orderBy($data['sort'], $data['sortOrder']);
                })
                ->get();
            return response()->json(
                ['status' => 'success', 'User' => $user],
                200
            );
        }
    }

    public function logsUserAll(Request $request)
    {
        /*  if ($request->user()->role_id != 1) {
            return response()->json(['error' => "Unauthorized "], 401);
        }*/

        // dd($request->limit);


        $data = $request->all();
        if (!empty($data['iniciodata'])) {
            $data['iniciodata'] = datadate($data['datainicio']);
        }
        if (!empty($data['fimdata'])) {
            $data['fimdata'] = datadate($data['fimdata']);
        }

        $logs = UserLog::from('logs_user as log')
            ->select('us.id as id_user', 'us.name as userName', 'log.id_log', 'act.log_description as log_description', 'log.ip_user', 'log.created_at as time_action', 'log.numatendimento', 'hos.uuid', 'hos.name as hospitalName', 'group.name as groupName')
            ->join('logs_action as act', 'act.id', '=', 'log.id_log')
            ->join('users as us', 'us.id', '=', 'log.id_user')
            ->leftJoin('hospitais as hos', 'hos.uuid', '=', 'log.uuid_hospital_atendimento')
            ->leftJoin('groups as group', 'group.id', '=', 'hos.grupo_id')
            ->when(!empty($request->datainicio), function ($query) use ($data) {
                return $query->whereDate('log.created_at', '>=', $data['datainicio']);
            })
            ->when(!empty($request->fimdata), function ($query) use ($data) {
                return $query->whereDate('log.created_at', '>=', $data['fimdata']);
            })
            ->when(!empty($request->name), function ($query) use ($data) {
                return $query->where('us.name', 'like', '%' . $data['name'] . '%');
            })
            ->when(!empty($request->procedencia), function ($query) use ($data) {
                return $query->where('hos.name', 'like', '%' . $data['procedencia'] . '%');
            })
            ->when(!empty($request->sort), function ($query) use ($data) {
                return $query->orderBy($data['sort'], $data['sortOrder']);
            })
            ->where('us.role_id', '!=', 1)
            ->paginate($request->limit);



        return response()->json(
            ['status' => 'success', 'Logs' => $logs],
            200
        );
    }

    public function listAllUser(Request $request)
    {
        if ($request->user()->role_id != 1) {
            return response()->json(['error' => "Unauthorized "], 401);
        }
        //Trazemos os usuarios que possui vinculo com hospitais
        $data = User::from('users as user')
            ->select('user.id', 'user.name', 'user.email', 'user.role_id', 'user.status')
            ->where('user.role_id', '!=', 1)
            ->get()
            ->toArray();

        $users = User::where('role_id', '!=', 1)->get();

        //Trazemos usuarios que não possui vinculo com hospitais
        $user_db = [];
        foreach ($users as $key => $user) {

            $user_nothos = UsersHospitals::where('id_user', $user->id)->first();

            if (empty($user_nothos) || empty($userlog)) {
                $user_db[$key]['id'] = $user->id;
                $user_db[$key]['name'] = $user->name;
                $user_db[$key]['email'] = $user->email;
            }
        }


        // Juntamos os usuários em uma só array
        $all_users = array_merge($data);


        //Rodamos o loop para trazer o ultimo log de cada usuário
        $retorno = [];
        foreach ($all_users as $key1 => $user_only) {
            //$user_only['permissoes'] = UserPermissoes::where('id_user', $user_only['id'])->select('id_permissao as id')->get();
            $user_only['dateLogin'] = UserLog::where('id_user', $user_only['id'])->orderBy('id_log', 'DESC')->first('created_at');
            // $user_only['hospitais'] = UsersHospitals::where('id_user', $user_only['id'])->get();
            $user_only['hospitais'] = UsersHospitals::from('users_hospitals as userhos')
                ->select('hos.id as id_hospital', 'hos.name as name', 'hos.uuid', 'hos.grupo_id', 'group.name as GroupName')
                ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                ->join('groups as group', 'group.id', '=', 'hos.grupo_id')
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
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized "], 401);
            }
        }
        $user = [];
        $user = User::findOrFail($request->id);

        if (!$user) {
            return response()->json([
                'message'   => 'The user can t be found',
            ], 404);
        } else {


            $user['hospitals'] = UsersHospitals::from('users_hospitals as userhos')
                ->select('hos.id', 'hos.name as name',  'hos.uuid', 'hos.grupo_id', 'group.name as groupName')
                ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                ->join('groups as group', 'group.id', '=', 'hos.grupo_id')
                ->where('id_user', $user->id)
                ->get();


            $user['permissoes'] = UserPermissoes::where('id_user', $user->id)->select('id_permissao as id')->get();

            return response()->json(
                ['status' => 'success',  'users' => $user],
                200
            );
        }
    }

    public function listUserGroups($id, Request $request)
    {
        $group = Groups::where('id', $id)->first();
        $user_auth = Auth::user();
        $user_group = UsersGroup::from('users_groups as usergroup')
            ->select('usergroup.id_group')
            ->join('groups as group', 'group.id', '=', 'usergroup.id_group')
            ->where('usergroup.id_user', $user_auth->id)
            ->first();


        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized "], 401);
            }
        }

        $data = User::from('users as user')
            ->select('user.id', 'user.name', 'user.email', 'user.role_id', 'user.status')
            ->where('user.role_id', '!=', 1)
            ->get()
            ->toArray();
        $users = User::where('role_id', '!=', 1)->get();



        // Trazemos usuarios que não possui vinculo com hospitais
        /* $user_db = [];
        foreach ($users as $key => $user) {
            // dd($user);
            $user_nothos = UsersHospitals::where('id_user', $user->id)->first();

            if (empty($user_nothos)) {
                return response()->json(
                    ['status' => 'Error', 'User dont belongs to group'],
                    400
                );
            }
        }*/


        // Juntamos os usuários em uma só array
        $all_users = array_merge($data);

        //Rodamos o loop para trazer o ultimo log de cada usuário
        $retorno = [];
        foreach ($all_users as $key1 => $user_only) {
            $user_only['permissoes'] = UserPermissoes::where('id_user', $user_only['id'])->select('id_permissao as id')->get();
            $user_only['dateLogin'] = UserLog::where('id_user', $user_only['id'])->orderBy('id_log', 'DESC')->first('created_at');
            // $user_only['hospitais'] = UsersHospitals::where('id_user', $user_only['id'])->get();
            $user_only['hospitais'] = UsersHospitals::from('users_hospitals as userhos')
                ->select('hos.id as id_hospital', 'hos.name as name', 'hos.uuid', 'hos.grupo_id')
                ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                ->where('id_user', '=', $user_only['id'])
                ->where('hos.grupo_id', $id)
                ->get();

            if (count($user_only['hospitais']) > 0) {
                $retorno[] = $user_only;
            }
        }

        return response()->json(
            ['status' => 'success', 'Group' => $group, 'Users' => $retorno],
            200
        );
    }

    public function getUsersHospital($id, Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized Access not administrator"], 401);
            }
        }

        $hospital = Hospitais::find($id);

        if (!$hospital) {
            return response()->json([
                'message'   => 'The Hospital can t be found',
            ], 404);
        } else {

            $hospital['users'] = UsersHospitals::from('users_hospitals as userhos')
                ->select('us.name', 'us.id', 'us.email', 'us.status')
                ->join('users as us', 'us.id', '=', 'userhos.id_user')
                ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                ->where('userhos.id_hospital', '=', $id)
                ->get();

            //Rodamos o loop para trazer o ultimo log de cada usuário
            $all_users = $hospital['users'];
            $retorno = [];

            foreach ($all_users as $key1 => $user_login) {
                $user_login['dateLogin'] = UserLog::where('id_user', $user_login['id'])->orderBy('id_log', 'DESC')->first('created_at');


                $retorno[] = $user_login;
            }


            return response()->json(
                ['status' => 'success', 'hospital' => $hospital],
                200
            );
        }
    }

    public function listUsersAdm(Request $request)
    {

        $idAuthUser = Auth::user();
        $user = User::where('id', $idAuthUser->id)->first();

        $id_hospitals = UsersHospitals::from('users_hospitals as user')
            ->select('user.id_hospital as id hospital')
            ->where('user.id_user', '=', $idAuthUser->id)
            ->get()
            ->toArray();

        $item = [];
        foreach ($id_hospitals as $key => $value) {
            $item[] = [
                'id' => $value
            ];
        }


        if (!$user->permission_user($user->id, 1)) {
            return response()->json(['error' => "Unauthorized"], 401);
        }

        $data = User::from('users as user')
            ->select('user.id', 'user.name', 'user.email', 'hos.name as hospital', 'hos.id as id_hospital')
            ->join('users_hospitals as user_hos', 'user_hos.id_user', '=', 'user.id')
            ->join('user_permissao as per', 'per.id_user', '=', 'user.id')
            ->join('hospitais as hos', 'hos.id', '=', 'user_hos.id_hospital')
            ->where('user.role_id', '!=', 1)
            ->where('user_hos.id_hospital', '=', $item)
            ->get()
            ->toArray();



        return response()->json(
            ['status' => 'success', 'Users' => $data],
            200
        );
    }

    public function updateImageUser(Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized "], 401);
            }
        }
        $array = ['error' => ''];

        //dd($request->all());
        $filename = '';
        $user = User::where('id', $request->id_user)->first();
        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $file_name = time() . '-' . $file->getClientOriginalName();
            $file_path = 'uploads/';

            $file->move($file_path, $file_name);

            if ($request->hasFile('image') != "") {
                $filename = $file_name;
            }
        }


        if ($user) {
            $user->image = $filename;
            $user->update();
            return response()->json(
                ['status' => 'success', 'Image uploaded succesfully'],
                200
            );
        } else {
            return response()->json(
                ['error' => 'User Not found'],
                404
            );
        }
    }

    public function approveUser($id, Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Unauthorized not administrator"], 401);
            }
        }
        $id = $request->id;
        $data = $request->only('name', 'phone', 'cpf', 'email');
        $permissions = $request->permissions;
        $hospitals = $request->hospitals;

        //Validar se email existe!
        $user = User::where('id', $id)->first();

        try {
            \DB::beginTransaction();

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

        //dd($user->email);

        $status = Password::sendResetLink(
            $request->only('email'),
        );

        if ($status == Password::RESET_LINK_SENT) {
            return [
                'status' => __($status),
                'message' => "User approved successfully!",
                'data' => $user
            ];
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
