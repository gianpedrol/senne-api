<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\ExameController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LaborController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\UserHospitalController;
use App\Http\Provider\ServiceProviderApi;
use Faker\Core\Uuid;
use PHPUnit\TextUI\XmlConfiguration\Group;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* TESTE DE API */

Route::get('/ping', function () {
    return ['pong' => true];
});
/* TESTE DE API FIM */


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//ROTA DE NÃO AUTORIZADO
Route::get('/401', [AuthController::class, 'unauthorized'])->name('login');
//ROTA DE LOGIN
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);

// password reset
Route::prefix('password')->group(function () {
    Route::post('send', [UserController::class, 'sendResetPassword']);
    Route::get('validation/', [UserController::class, 'verifyResetRoute'])->name('verifyResetRoute');
    Route::post('reset/{id}', [UserController::class, 'reset'])->name('reset');
});

//Rota de registro de usuario Master
Route::post('auth/register', [AuthController::class, 'create']);

//Rota relacionada ao laboratório via usuario Senne
Route::middleware('auth:api')->group(function () {

    /* GRUPOS*/
    //Cria um novo grupo
    Route::post('register/group', [GroupController::class, 'storeGroup']);
    //Lista Grupos da api
    Route::get('list/procedencia/groups', [GroupController::class, 'getGroups']);
    //lISTA GROUPS DO NOSSO BANCO DE DADOS
    Route::get('list/groups', [GroupController::class, 'listGroups']);
    //Rota de edição do Grupo
    Route::put('edit/group/{id}', [GroupController::class, 'updateGroup']);
    //LISTA HOSPITAIS DE UM GRUPO
    Route::get('list/hospitals/group/{id}', [GroupController::class, 'getHospitalsGroup']);


    /**GRUPO DE ROTAS USUARIO GROUP */
    Route::prefix('group/user')->group(function () {
        /**USUÁRIOS */
        //rota para criação de usuário Group
        Route::post('/store', [UserGroupController::class, 'storeUserGroup']);
        //rota para listar de usuários
        Route::get('show/{id}', [UserGroupController::class, 'showUserGroup']);
        //rota para listar de usuários do Grupo
        Route::get('list/group/{id}', [GroupController::class, 'getUsersGroup']);
        //rota para edição de usuário comum
        Route::put('edit/{id} ', [UserGroupController::class, 'updateUserGroup']);

        //Lista Resultados de um usuario
        Route::post('list/results/user', [UserGroupController::class, 'getResultsUser']);
    });


    /* HOSPITAIS */
    //salva hospitais
    Route::post('hospital/store', [HospitalController::class, 'storeHospital']);
    //lista hospitais
    Route::get('list/hospitals', [HospitalController::class, 'listHospitals']);
    //ATUALIZA HOSPITAL
    Route::put('hospital/{id}', [HospitalController::class, 'updateHospital']);
    //Lista procedencias vindo da api
    Route::get('list/procedencia', [HospitalController::class, 'getProcedencia']);

    Route::prefix('hospital/user')->group(function () {
        //cria usuarios hospital
        Route::post('/store', [UserHospitalController::class, 'storeUserHospital']);
        //LISTA USUARIOS DE UM HOSPITAL
        Route::get('/list/{id}', [UserHospitalController::class, 'getUsersHospital']);
        //rota para mostrar usuário
        Route::get('show/{id}', [UserHospitalController::class, 'showUserGroup']);
        //Rota de edição do Usuário do Hospital
        Route::put('edit/{id}', [UserHospitalController::class, 'updateUserHospital']);
    });


    /*EXAMES E RESULTADOS */
    //Lista de exames
    Route::get('list/exames', [ExameController::class, 'listExame']);
    //Lista de exames
    Route::post('list/results', [ExameController::class, 'resultExame']);
    //Lista de exames por atendimento
    Route::get('/treatment/{uuid}/{atendimento}', [ExameController::class, 'listAtendimentos']);
    //Lista de exames por atendimento
    Route::get('/hospitals/treatment/{uuid}/{startdate}/{finaldate}', [ExameController::class, 'listAtendimentosDate']);


    //Lista Resultados de um usuario
    Route::get('list/logs/user/{id}', [UserController::class, 'logsUser']);
    //Lista Resultados de um usuario
    Route::get('list/users', [UserController::class, 'listAllUser']);
    //Edita Usuário
    Route::put('edit/user/{id}', [UserController::class, 'update']);

    //cria usuario
    Route::post('user/create', [UserController::class, 'createUser']);
    //rota para mostrar usuário
    Route::get('show/user/{id}', [UserController::class, 'showUser']);

    //rota para mostrar usuário
    Route::get('adm/list/users', [UserController::class, 'listUsersAdm']);
});
