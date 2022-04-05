<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LaborController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\UserGroupController;
use App\Http\Provider\ServiceProviderApi;
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

//Rota de registro de usuario Master
Route::post('auth/register', [AuthController::class, 'create']);


/*Route::middleware('auth')->group(function() {
    Route::post('labor/store',[\App\Http\Controllers\LaborController::class,'store']);
});*/


// password reset
Route::prefix('password')->group(function () {
    Route::post('send', [UserController::class, 'sendResetPassword']);
    Route::get('validation/', [UserController::class, 'verifyResetRoute'])->name('verifyResetRoute');
    Route::post('reset/{id}', [UserController::class, 'reset'])->name('reset');
});

//Rota relacionada ao laboratório via usuario Senne
Route::middleware('auth:api')->group(function () {

    /* GRUPOS*/
    //Cria um novo grupo
    Route::post('register/group', [GroupController::class, 'storeGroup']);
    //Lista Grupos da api
    Route::get('list/groups', [GroupController::class, 'getGroups']);
    //Rota de edição do Grupo
    Route::put('edit/group/{id}', [GroupController::class, 'updateGroup']);
    //LISTA HOSPITAIS DE UM GRUPO
    Route::get('list/hospitals/group/{id}', [GroupController::class, 'getHospitalsGroup']);
    //CRIA USUARIOS GRUPO
    Route::post('group/store/user', [GroupController::class, 'storeUser']);


    /* HOSPITAIS */
    //salva hospitais
    Route::post('hospital/store', [HospitalController::class, 'storeHospital']);
    //lista hospitais
    Route::get('list/hospitals', [HospitalController::class, 'listHospitals']);
    //ATUALIZA HOSPITAL
    Route::put('hospital/{id}', [HospitalController::class, 'updateHospital']);
    //cria usuarios hospital
    Route::post('hospital/store/user', [HospitalController::class, 'storeUserHospital']);
    //LISTA USUARIOS DE UM HOSPITAL
    Route::get('list/hospitals/users/{id}', [HospitalController::class, 'getUsersHospital']);
    //Rota de edição do Usuário do Hospital
    Route::put('edit/hospital/user/{id}', [HospitalController::class, 'updateUserHospital']);


    //Salva Laboratórios
    Route::post('labor/store', [LaborController::class, 'store']);

    //Salva Usuário Laboratório
    Route::post('labor/store/user', [LaborController::class, 'storeUser']);

    //Lista Usuários Laboratórios
    Route::get('list/labor/users', [LaborController::class, 'listUserLabors']);

    //lista laboratórios
    Route::get('list/labors', [LaborController::class, 'listLabors']);



    //rota para criação de usuário comum
    Route::post('create/user/store', [UserGroupController::class, 'createUserGroup']);

    //rota para listar de usuários
    Route::get('list/users', [UserGroupController::class, 'listUserGroup']);

    //rota para listar de usuários
    Route::get('list/user/{id}', [UserGroupController::class, 'listUserGroup']);



    //rota para edição de usuário comum
    Route::put('edit/user/{id}', [UserGroupController::class, 'updateUserGroup']);
    //Lista procedencias vindo da api
    Route::get('list/procedencia', [HospitalController::class, 'getProcedencia']);
});
