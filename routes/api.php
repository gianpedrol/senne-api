<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LaborController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\UserGroupController;

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
Route::get('/ping', function(){
    return ['pong'=>true];
});
/* TESTE DE API FIM */


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//ROTA DE NÃO AUTORIZADO
Route::get('/401', [AuthController::class, 'unauthorized'])->name('login');

//ROTA DE LOGIN
Route::post('auth/login',[AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class,'logout']);

//Rota de registro de usuario Master
Route::post('auth/register',[AuthController::class, 'create']);


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
Route::middleware('auth')->group(function() {

    //Salva Laboratórios
	Route::post('labor/store', [LaborController::class, 'store']);

    //Salva Usuário Laboratório
	Route::post('labor/store/user', [LaborController::class, 'storeUser']);

    //Lista Usuários Laboratórios
    Route::get('list/labor/users', [LaborController::class, 'listUserLabors']);

    //lista laboratórios
    Route::get('list/labors', [LaborController::class, 'listLabors']);

    //salva hospitais
	Route::post('hospital/store', [HospitalController::class, 'storeHospital']);

    //lista hospitais
    Route::get('list/hospitals', [HospitalController::class, 'listHospitals']);

    //cria usuarios hospital
    Route::post('hospital/store/user', [HospitalController::class, 'storeUserHospital']);


    //rota para criação de usuário comum
    Route::post('create/user/store', [UserGroupController::class, 'createUserGroup']);

});




