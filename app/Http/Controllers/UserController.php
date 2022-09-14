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
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Mail\emailProtocolMail;
use App\Mail\emailUpdatePermissions;
use App\Mail\emailWelcome;
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
use App\Models\StorePDF;
use App\Models\UsersGroup;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Password;
use Barryvdh\DomPDF\Facade\Pdf;
use PHPUnit\TextUI\XmlConfiguration\Group;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Storage;
use Svg\Tag\Path;

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

class UserController extends Controller
{

    /**
     * @OA\Post(
     * path="/api/user/create",
     * operationId="Register User Hospital - Inside Platform",
     * tags={"Register User Hospital - Inside Platform"},
     * summary="Register User Hospital - Inside Platform",
     * description="Register User Hospital - Inside Platform ",
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
     *               @OA\Property(property="crm", type="text"),
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
            return response()->json(['error' =>"User already exists!"], 400);
        }

        /* CHECAR SE EMAIL CONFERE COM DOMINIO */
        $userEmail = $data['email'];
        $dominio = explode('@', $userEmail);
        //dd($dominio[1]);
        $domainEmail = $dominio[1];

        

        $hospital = Hospitais::where('id', $hospitalsId)->first();
        $hospital['sDomain'] = DomainHospital::from('domains_hospitals as domain')
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
        }
        if(empty($hospitalsDomain)){
            $hospitalsCheck = true;
        }
               if ( $hospitalsCheck = true) {

                    try {
                        \DB::beginTransaction();
        
                        //Define nivel user Senne
                        $role_id = 2;
        
                      //  $senha_md5= Str::random(8);//Descomentar após testes
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
        
        
                        /* Salva mais de um hospital ao usuário*/
                        UsersHospitals::where('id_user', $newUser->id)->delete(); //Deleta os registros
                        if (!empty($hospitalsId)) {
                            foreach ($hospitalsId  as $id_hospital) {
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
        
                        $status = Password::sendResetLink(
                            $request->only('email'),
                        );
        
                        if ($status == Password::RESET_LINK_SENT) {
                            Mail::to($request->only('email'))->send(new emailWelcome($data));
                            return [
                                'status' => __($status),
                                'message' => "Uusário registrado com sucesso!", 'data' => $newUser
                            ];
                        }
        
                        throw ValidationException::withMessages([
                            'email' => [trans($status)],
                        ]);
                    } catch (\Throwable $th) {
                        //dd($th->getMessage());
                        \DB::rollback();
                        return ['error' => 'Não foi possivel salvar no banco de dados', 'erro' => $th->getMessage(), 400];
                    }
                } else {
                    return response()->json(['error' => 'Esse dominio é inválido para este hospital'], 400);
                }
      
    }

    /**
     * @OA\Put(
     * path="edit/user/{id}",
     * operationId="Update User Hospital",
     * tags={"Update User Hospital"},
     * summary="Update User Hospital",
     * description="Update User Hospital ",
     *      @OA\Parameter(
     *      name="id",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
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
    public function update(Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Não autorizado"], 401);
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
                Mail::to($user->email)->send(new emailUpdatePermissions($data));
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
           // dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Não foi possivel salvar no banco de dados', 'erro' => $th->getMessage(), 400];
        }


        return response()->json(['message' => 'Uusário atualizado com sucesso!']);
    }
    /**
     * 
     * @OA\Del(
     * path="delete/user/{id}",
     * operationId="Delete User",
     * tags={"Delete User"},
     * summary="Delete User",
     * description="Delete User ",
     *      @OA\Parameter(
     *      name="id",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   )
     *      @OA\Response(
     *          response=201,
     *          description="user successfully deleted",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="user successfully deleted",
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
    public function delete(Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Não Autorizado"], 401);
            }
        }
        $id = $request->id;

        try {
            $user = User::findOrFail($id)->delete();
            return response()->json(['message' => 'Usuário deletado'], 200);
            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->ip_user = $request->ip();
            $saveLog->id_log = 5;
            $saveLog->save();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Não foi possível deletar o usuário', $e], 400);
        }
    }

    /**
     * @OA\Put(
     * path="/api/inactivate/user/{id}",
     * operationId="Inactive User ",
     * tags={"Inactive User "},
     * summary="Inactive User ",
     * description="Inactive User  ",
     *      @OA\Parameter(
     *      name="id",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *            @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"status"},
     *               @OA\Property(property="status", type="number"),
     *           )
     *          )
     *        ),
     *      @OA\Response(
     *          response=201,
     *          description="User inactivated successfully!",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="User inactivated successfully!",
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
    public function inactivateUser($id, Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Não Autorizado"], 401);
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
                return response()->json(['message' => 'Usuário inativado com sucesso'], 200);
            } else {
                return response()->json(['error' => 'Não foi possível inativar usuário'], 400);
            }

            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->ip_user = $request->ip();
            $saveLog->id_log = 12;
            $saveLog->save();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Não foi possível inativar usuário', $e], 400);
        }
    }

    /**
     * @OA\Get(
     *   tags={"Logs User "},
     *   path="list/logs/user/{id}",
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
    public function logsUser(Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Não Autorizado"], 401);
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
                'message'   => 'Uusário não encontrado',
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
                ->orderBy('log.created_at','DESC')
                ->get();
            return response()->json(
                ['status' => 'success', 'User' => $user],
                200
            );
        }
    }
    /**
     * @OA\Get(
     *   tags={"All Logs Users "},
     *   path="list/logs/users",
     *   summary="Summary",
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function logsUserAll(Request $request)
    {
        if ($request->user()->role_id != 1) {
            return response()->json(['error' => "Unauthorized "], 401);
        }

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
            ->orderBy('log.created_at','DESC')
            ->paginate($request->limit);



        return response()->json(
            ['status' => 'success', 'Logs' => $logs],
            200
        );
    }
    /**
     * @OA\Get(
     *   tags={"All Users "},
     *   path="list/users",
     *   summary="Summary",
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function listAllUser(Request $request)
    {
        if ($request->user()->role_id != 1) {
            return response()->json(['error' => "Não Autorizado"], 401);
        }

        $data = $request->all();
        //$status = explode(',', $request->status);
        $data['status'] = explode(',', $request->status);


        $per_page = (isset($request->per_page) && $request->per_page > 0) ? $request->per_page : 10;

        $sort = (isset($request->per_page) && !empty($request->sort)) ? $request->sort : 'id';


        //Trazemos os usuarios que possui vinculo com hospitais
        $all_users = User::from('users as user')
            ->select(
                'user.id',
                'user.name',
                'user.email',
                'user.status',
                'user.crm',
                'user.role_id'
            )
            ->where('user.role_id', '!=', 1)
            ->when(!empty($request->name), function ($query) use ($data) {
                return $query->where('user.name', 'like', '%' . $data['name'] . '%');
            })
            ->when(!empty($request->status), function ($query) use ($data) {
                return $query->whereIn('user.status', $data['status']);
            })
            ->when(!empty($request->orderby) && $sort == 'id', function ($query) use ($data) {
                return $query->orderBy('id', $data['orderby']);
            })
            ->when(!empty($request->orderby) && $sort == 'name', function ($query) use ($data) {
                return $query->orderBy('name', $data['orderby']);
            })
            ->paginate($per_page);

        //dd($all_users);


        //Rodamos o loop para trazer o ultimo log de cada usuário
        $retorno = [];
        foreach ($all_users as $key1 => $user_only) {
            $user_only['dateLogin'] = UserLog::where('id_user', $user_only['id'])->orderBy('id_log', 'DESC')->first('created_at');
            $user_only['hospitais'] = UsersHospitals::from('users_hospitals as userhos')
                ->select('hos.id as id_hospital', 'hos.name as name', 'hos.uuid', 'hos.grupo_id', 'group.name as GroupName')
                ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                ->join('groups as group', 'group.id', '=', 'hos.grupo_id')
                //->where('id_user', $user_only['id'])
                ->when(!empty($request->procedencia), function ($query) use ($data) {
                    return $query->where('hos.name', 'like', '%' . $data['procedencia'] . '%');
                })
                ->first();
            $retorno[] = $user_only;
        }

        $all_users = $all_users->toArray();

        //Construct paginate info
        $paginate['first_page_url'] = $all_users['first_page_url'];
        $paginate['from'] = $all_users['from'];
        $paginate['last_page'] = $all_users['last_page'];
        $paginate['next_page_url'] = $all_users['next_page_url'];
        $paginate['path'] = $all_users['path'];
        $paginate['per_page'] = $all_users['per_page'];
        $paginate['prev_page_url'] = $all_users['prev_page_url'];
        $paginate['to'] = $all_users['to'];
        $paginate['total'] = $all_users['total'];

        return response()->json(
            ['status' => 'success', 'Users' => $all_users],
            200
        );
    }
    /**
     * @OA\Get(
     *   tags={"User Information "},
     *   path="/api/show/user/{id}",
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
    public function showUser(Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Não Autorizado"], 401);
            }
        }
        $user = [];
        $user = User::findOrFail($request->id);

        if (!$user) {
            return response()->json([
                'message'   => 'Não foi possível encontrar o usuario',
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
    /**
     * @OA\Get(
     *   tags={"All Users of Group "},
     *   path="/api/list/group/user/{id}",
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
    public function listUserGroups($id, Request $request)
    {
        $group = Groups::where('id', $id)->first();
        $user_auth = Auth::user();
        $user_auth['hospitals'] = UsersHospitals::from('users_hospitals as userhos')
        ->select('hos.id', 'hos.grupo_id', 'hos.name as name',  'hos.uuid')
        ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
        ->where('id_user', $user_auth->id)
        ->get();


        if ($request->user()->role_id != 1) {

            foreach ($user_auth['hospitals'] as $userGroupId){
                if ($userGroupId['grupo_id'] != $id ) {                
                    return response()->json(['error' => " Não Autorizado "], 401);
                }
            }
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => " Não Autorizado "], 401);
            }
        }


        $user_group = UsersGroup::from('users_groups as usergroup')
            ->select('usergroup.id_group')
            ->join('groups as group', 'group.id', '=', 'usergroup.id_group')
            ->where('usergroup.id_user', $user_auth->id)
            ->first();
        



        $data = $request->all();

        $data['status'] = explode(',', $request->status);

        $per_page = (isset($request->per_page) && $request->per_page > 0) ? $request->per_page : 10;

        $sort = (isset($request->per_page) && !empty($request->sort)) ? $request->sort : 'id';
        $authUser = Auth::user();
        $allUsers = User::from('users as user')
            ->select('user.id', 'user.name', 'user.email', 'user.role_id', 'user.status')
            ->where('user.role_id', '!=', 1)
            ->where('user.role_id', '=', 2)
            ->where('user.id', '!=', $authUser->id)
            ->when(!empty($request->name), function ($query) use ($data) {
                return $query->where('user.name', 'like', '%' . $data['name'] . '%');
            })
            ->when(!empty($request->status), function ($query) use ($data) {
                return $query->whereIn('user.status', $data['status']);
            })
            ->when(!empty($request->orderby) && $sort == 'id', function ($query) use ($data) {
                return $query->orderBy('id', $data['orderby']);
            })
            ->when(!empty($request->orderby) && $sort == 'name', function ($query) use ($data) {
                return $query->orderBy('name', $data['orderby']);
            })
            ->paginate($per_page);

            //dd($allUsers);
        //$users = User::where('role_id', '!=', 1)->get();



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
        // $all_users = array_merge($allUsers);

        //Rodamos o loop para trazer o ultimo log de cada usuário
        $retorno = [];
        foreach ($allUsers as $key1 => $user_only) {
            
            $user_only['permissoes'] = UserPermissoes::where('id_user', $user_only['id'])->select('id_permissao as id')->get();
            $user_only['dateLogin'] = UserLog::where('id_user', $user_only['id'])->orderBy('id_log', 'DESC')->first('created_at');
            // $user_only['hospitais'] = UsersHospitals::where('id_user', $user_only['id'])->get();
            $user_only['hospitais'] = UsersHospitals::from('users_hospitals as userhos')
                ->select('hos.id as id_hospital', 'hos.name as name', 'hos.uuid', 'hos.grupo_id', 'group.name as GroupName')
                ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                ->join('groups as group', 'group.id', '=', 'hos.grupo_id')
                  ->where('hos.grupo_id', $id)
                ->when(!empty($request->procedencia), function ($query) use ($data) {
                    return $query->where('hos.name', 'like', '%' . $data['procedencia'] . '%');
                })
                ->first();

                $retorno[] = $user_only;
        }

        $all_users = $allUsers->toArray();

        

        //Construct paginate info
       $paginate['first_page_url'] = $all_users['first_page_url'];
        $paginate['from'] = $all_users['from'];
        $paginate['last_page'] = $all_users['last_page'];
        $paginate['next_page_url'] = $all_users['next_page_url'];
        $paginate['path'] = $all_users['path'];
        $paginate['per_page'] = $all_users['per_page'];
        $paginate['prev_page_url'] = $all_users['prev_page_url'];
        $paginate['to'] = $all_users['to'];
        $paginate['total'] = $all_users['total'];

        return response()->json(['status' => 'success', 'Group' => $group, 'Users' => $allUsers],
            200
        );
    }
    /**
     * @OA\Get(
     *   tags={"All Users of Hospital "},
     *   path="/api/hospital/user/list/{id}",
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
    public function getUsersHospital($id, Request $request)
    {

        $user_auth = Auth::user();
        $user_auth['hospitals'] = UsersHospitals::from('users_hospitals as userhos')
        ->select('hos.id', 'hos.grupo_id', 'hos.name as name',  'hos.uuid')
        ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
        ->where('id_user', $user_auth->id)
        ->get();
        
        if ($request->user()->role_id != 1) {
            foreach ($user_auth['hospitals'] as $userHospitalId){
                if ($userHospitalId['id'] != $id ) {                
                    return response()->json(['error' => "Não Autorizado "], 401);
                }
            }
    
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Não Autorizado"], 401);
            }
        }



        $hospital = Hospitais::find($id);

        if (!$hospital) {
            return response()->json([
                'message'   => 'Hospital não encontrado',
            ], 404);
        } else {
            $data = $request->all();
            //$status = explode(',', $request->status);
            $data['status'] = explode(',', $request->status);


            $per_page = (isset($request->per_page) && $request->per_page > 0) ? $request->per_page : 10;

            $sort = (isset($request->per_page) && !empty($request->sort)) ? $request->sort : 'id';

            $authUser = Auth::user();
            $hospital['users'] = UsersHospitals::from('users_hospitals as userhos')
                ->select('us.name', 'us.id', 'us.email', 'us.status','us.role_id')
                ->join('users as us', 'us.id', '=', 'userhos.id_user')
                ->join('hospitais as hos', 'userhos.id_hospital', '=', 'hos.id')
                ->where('userhos.id_hospital', '=', $id)
                ->where('us.role_id', '!=', 1)
                ->where('us.role_id', '=', 2)
                ->where('us.id', '!=', $authUser->id)
                ->when(!empty($request->name), function ($query) use ($data) {
                    return $query->where('user.name', 'like', '%' . $data['name'] . '%');
                })
                ->when(!empty($request->status), function ($query) use ($data) {
                    return $query->whereIn('user.status', $data['status']);
                })
                ->when(!empty($request->orderby) && $sort == 'id', function ($query) use ($data) {
                    return $query->orderBy('id', $data['orderby']);
                })
                ->when(!empty($request->orderby) && $sort == 'name', function ($query) use ($data) {
                    return $query->orderBy('name', $data['orderby']);
                })
                ->paginate($per_page);

            //Rodamos o loop para trazer o ultimo log de cada usuário
            $all_users = $hospital['users'];
            $retorno = [];

            foreach ($all_users as $key1 => $user_login) {
                $user_login['dateLogin'] = UserLog::where('id_user', $user_login['id'])->orderBy('id_log', 'DESC')->first('created_at');
                $user_login['permissoes'] = UserPermissoes::where('id_user', $user_login['id'])->select('id_permissao as id')->get();


                $retorno[] = $user_login;
            }

            $all_users = $all_users->toArray();

            //Construct paginate info
            $paginate['first_page_url'] = $all_users['first_page_url'];
            $paginate['from'] = $all_users['from'];
            $paginate['last_page'] = $all_users['last_page'];
            $paginate['next_page_url'] = $all_users['next_page_url'];
            $paginate['path'] = $all_users['path'];
            $paginate['per_page'] = $all_users['per_page'];
            $paginate['prev_page_url'] = $all_users['prev_page_url'];
            $paginate['to'] = $all_users['to'];
            $paginate['total'] = $all_users['total'];


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

    /**
     * @OA\Post(
     * path="/api/upload/user/image",
     * operationId="Upload User Image",
     * tags={"Upload User Image"},
     * summary="Upload User Image",
     * description="Upload User Image ",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *            @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"image", "id_user"},
     *               @OA\Property(property="image", type="text"),
     *               @OA\Property(property="id_user", type="number"),         
     *            ),
     *           )
     *        ),
     *      @OA\Response(
     *          response=201,
     *          description="Image registered successfully!",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Image registered successfully!",
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
    public function updateImageUser(Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Não Autorizado"], 401);
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
                ['status' => 'success', 'Imagem atualizada com sucesso!'],
                200
            );
        } else {
            return response()->json(
                ['error' => 'Usuário não encontrado'],
                404
            );
        }
    }

    /**
     * @OA\Post(
     * path="/api/approve/user/{id}",
     * operationId="Approve User",
     * tags={"Approve User"},
     * summary="Approve User",
     * description="Approve User ",
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
     *               @OA\Property(property="crm", type="text"),
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
    public function approveUser($id, Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Não Autorizado"], 401);
            }
        }
            
        $user = User::where('id', $request->id)->first();
        if ($user) {
             try {
                \DB::beginTransaction();


                    User::where('id', $request->id)->update(['status' => 2]);
                
                

                $data = ['email' =>$user->email, 'name' => $user->name];
                //GERA LOG
                $log = Auth::user();
                $saveLog = new UserLog();
                $saveLog->id_user = $log->id;
                $saveLog->ip_user = $request->ip();
                $saveLog->id_log = 11;
                $saveLog->save();

                \DB::commit();
                $status = Password::sendResetLink(
                  [
                    'email'  =>  $user->email,
                    ]
                );
    
                if ($status == Password::RESET_LINK_SENT) {
                    Mail::to($data['email'])->send(new emailWelcome($data));
                    return [
                        'status' => __($status),
                        'message' => "Usuário aprovado com sucesso!",
                        'data' => $user
                    ];
                }
    
                throw ValidationException::withMessages([
                    'email' => [trans($status)],
                ]);
            } catch (\Throwable $th) {
              //  dd($th->getMessage());
                \DB::rollback();
                return ['error' => 'Não foi possivel salvar no banco de dados', 'erro' => $th->getMessage(), 400];
            }

        }else{
            
            return response()->json(['error' => 'Usuário não encontrado'],400);
        }
       
    }
    public function approveDoctorUser($id, Request $request)
    {
        if ($request->user()->role_id != 1) {
            if (!$request->user()->permission_user($request->user()->id, 1)) {
                return response()->json(['error' => "Não Autorizado"], 401);
            }
        }
        $codmedico = $request->codmedico;
        $uuidmedico = $request->hash_medico;

        //Validar se email existe!
        $user = User::where('id', $id)->first();

        if ($user->role_id != 4) {
            return response()->json(['error' => "Você não pode aprovar o usuário nessa rota"], 401);
        }



        try {
            \DB::beginTransaction();

            $user = User::where('id', $id)->first()->update(['status' => 2, 'cod_doctor' => $codmedico, 'uuid_doc' => $uuidmedico]);


            //GERA LOG
            $log = Auth::user();
            $saveLog = new UserLog();
            $saveLog->id_user = $log->id;
            $saveLog->ip_user = $request->ip();
            $saveLog->id_log = 11;
            $saveLog->save();

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollback();
            return ['error' => 'Não foi possivel salvar no banco de dados', 'erro' => $th->getMessage(), 400];
        }

        $status = Password::sendResetLink(
            $request->only('email'),
        );

        if ($status == Password::RESET_LINK_SENT) {
            return [
                'status' => __($status),
                'message' => "Usuário não aprovado!",
                'data' => $user
            ];
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function listDoctorUserApi(Request $request)
    {


        $crm = $request->crm;
        $ufCrm = $request->ufcrm;


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
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/pesq_medico?CrmMedico=' .  $crm . '&UFCrmMedico=' . $ufCrm);


        return $response;
    }

    public function printProtocol(Request $request)
    {



        $data = $request->only(['login_protocol', 'passtemp','r_id', 'name']);
        $r_id = $request->r_id;
        $user = User::where('login_protocol', $data['login_protocol'])->first();

    /*    $files = glob('pdf/*.*');

        if (count($files) >= 10) {
            foreach ($files as $file) {
                unlink($file);
            }
            DB::table('table_pdf_value')->delete();
        } */


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
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearer
        ])->get('http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio_teste/valida_senha_paciente?UsuarioPaciente='.$data['login_protocol'].'&SenhaPaciente='. $data['passtemp']);

       
        $items = json_decode($response->getBody());

        $patientUuid = $items->Hash;

        try {
            \DB::beginTransaction();

            $senha_md5 =  $data['passtemp'];
            $senha_temp = bcrypt($senha_md5);

            if (empty($user)) {
                //Define nivel user Senne
                $role_id = 5;

                $newUser = new User();
                $newUser->name = $data['name'];
                $newUser->login_protocol = $data['login_protocol'];
                $newUser->role_id = $role_id;
                $newUser->password = $senha_temp;
                $newUser->cod_pf = $patientUuid;
                $newUser->save();

                $log = Auth::user();
                $saveLog = new UserLog();
                $saveLog->id_user = $log->id;
                $saveLog->ip_user = $request->ip();
                $saveLog->id_log = 14 ;
                $saveLog->save();
                                
               
             /*   $pdf = PDF::loadView('pdf.protocol', compact('data', 'senha_md5'))->setPaper('a4');*/

            }else{

                $senha_md5 =  $data['passtemp'];
                $senha_temp = bcrypt($senha_md5);
                
                $user->update(['password' =>  $senha_temp ]);
        
          /*      $pdf = PDF::loadView('pdf.protocol', compact('data', 'senha_md5'))->setPaper('a4');*/

            }

            \DB::commit();
        } catch (\Throwable $th) {
            //dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Não foi possivel salvar no banco de dados', 'erro' => $th->getMessage(), 400];
        }

    /*    $numPDF = Str::random(9);
        
        
       $pdf->save(public_path('pdf/protocol' . $numPDF . '.pdf'));

        try {
            \DB::beginTransaction();

            StorePDF::create(['pdf' => 'protocol' . $numPDF . '.pdf']);

            \DB::commit();
        } catch (\Throwable $th) {
            dd($th->getMessage());
            \DB::rollback();
            return ['error' => 'Could not write data', 400];
        }*/

     if(!empty($newUser->email)){
            try {
                /* Enviar e-mail para o usuário com sua senha de acesso */
                Mail::to($newUser->email)->send(new emailProtocolMail($data));
                                
              //  $value =  StorePDF::where('pdf', 'protocol' . $numPDF . '.pdf')->first();
                return response()->json(['http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/protocolo?Hash='. $r_id], 200);
            } catch (Exception $ex) {
                //dd($ex);
                return response()->json(['error' => 'Não foi possível enviar', $ex], 500);
            }
        }else{
          //  $value =  StorePDF::where('pdf', 'protocol' . $numPDF . '.pdf')->first();             
                
            return response()->json(['http://sistemas.senneliquor.com.br:8804/ords/gateway/apoio/protocolo?Hash='. $r_id], 200);
        }

    }

    public function resendEmailActivateUser(Request $request){

        $email = $request->only('email');

        $user = User::where('email', $email['email'])->first();

        if(empty($user)){
            return response()->json(['error' => 'Uusário não encontrado'], 404);
        }else{
            $data = $user->email;
           
            try{
    
                $status = Password::sendResetLink(
                    $request->only('email'),
                );
    
                if ($status == Password::RESET_LINK_SENT) {
                    Mail::to($request->only('email'))->send(new emailWelcome($data));
                    return [
                        'status' => __($status),
                        ];
                }
    
                throw ValidationException::withMessages([
                    'email' => [trans($status)],
                ]);
            } catch (\Throwable $th) {
             //   dd($th->getMessage());
                \DB::rollback();
                return ['error' => 'Não foi possivel salvar no banco de dados', 'erro' => $th->getMessage(), 400];
            }
            
        }
    }    
}
