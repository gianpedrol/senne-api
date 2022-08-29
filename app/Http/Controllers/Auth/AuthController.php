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
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/* 
Status 
/***
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



class AuthController extends Controller
{



    /**
     * @OA\Post(
     * path="/api/login/{role_id}",
     * operationId="authLogin",
     * tags={"Login"},
     * summary="User Login",
     * description="Login User Here",
     *      @OA\Parameter(
     *      name="role_id",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email", "password"},
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="password", type="password")
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Login Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Login Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function login(Request $request, $id)
    {
        
        $user = User::where('email', $request->email)->first();

        if(empty($user)){
            return response()->json(['status'=> 'error', 'message' => 'the login is wrong' ], 401  );
        }
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
            }else{
                return response()->json([
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
         /**CASO SEJA ROLE ID 5 USUARIO DE ATENDIMENTO */
         if($id == 5){
            $credentialsProtocol = $request->only('login_protocol', 'password');
            
            $userProtocol = User::where('login_protocol', $credentialsProtocol['login_protocol'])->first();            
            $passVerication = Hash::check($request->password,  $userProtocol->password);

            if(empty($userProtocol) ||  $passVerication == false){
                return response()->json(['status'=> 'error', 'message' => 'the login is wrong' ], 401  );
            }
            if ($userProtocol->role_id == 5) {             
                
                $token = Auth::attempt($credentialsProtocol);
                 if ($token) {
                     $array['token'] = $token;
                 } else {
                     $array['message'] = 'Incorrect username or password';
                 }
 
                 if (!$user) {
                     return response()->json([
                         'message'   => 'The user can t be found',
                     ], 404);
                 } else {
                     return response()->json(['message' => "User Logged in!", 'token' => $array['token'], 'user' => $userProtocol], 200);
                 }
             }             
            }
            /** FIM ROLE ID 5  */

        $userLogin = User::where('email', $request->email)->first();
        
        
        
        if ($userLogin->role_id == $id) {  
            
            
            $user = User::where('email', $request->email)->first();
            $passwordVerication = Hash::check($request->password, $user->password);
           
            if(empty($user) || $passwordVerication == false ){
                return response()->json(['status'=> 'error', 'message' => 'the login is wrong' ], 401  );
            }

                $token = auth()->login($user);
                if ($token) {
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
            $user =  User::where('email', $request->email)->first();
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

    
    /**
     * @OA\Post(
     * path="/api/auth/login/{id}",
     * operationId="authRegister",
     * tags={"RegisterUserMaster"},
     * summary="Register User Master",
     * description="Create the User Master Here",
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
     * 
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="User Master Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="User Master Created Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     * @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */

    public function create(Request $request)
    {

        /*
            Método responsável por gravar os usuários da Senne, esses uauários possui permissão role_id 1
            ele tem acesso a todo o conteudo do sistema
        */

        $data = $request->only(['name', 'cpf', 'email', 'cnpj', 'phone']);

        $user = User::where('email', $data['email'])->first();

        if (!empty($user)) {
            return response()->json(['error' =>"User already exists!"], 400);
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
    /**
     * @OA\Post(
     * path="/api/auth/logout",
     * operationId="Logout",
     * tags={"Logout"},
     * summary="Logout",
     * description="Logout",
 
     *      @OA\Response(
     *          response=201,
     *          description="Logged out Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description= "Logged out  Successfully",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
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
