<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LaborController;
use App\Http\Controllers\HospitalController;

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
Route::post('auth/register',[AuthController::class, 'create']);
Route::post('auth/logout', [AuthController::class,'logout']);

/*Route::middleware('auth')->group(function() {
    Route::post('labor/store',[\App\Http\Controllers\LaborController::class,'store']);
});*/


// password reset
Route::prefix('password')->group(function () {
    Route::post('send', [UserController::class, 'sendResetPassword']);
    Route::get('validation/', [UserController::class, 'verifyResetRoute'])->name('verifyResetRoute');
    Route::post('reset/{id}', [UserController::class, 'reset'])->name('reset');
});

//Rota relacionada ao laboratório via usuario Senni
Route::middleware('auth')->group(function() {
	Route::post('labor/store', [LaborController::class, 'store']);
	Route::post('labor/store/user', [LaborController::class, 'storeUser']);

    //lista laboratórios
    Route::get('list/labors', [LaborController::class, 'listLabors']);

    //salva hospitais
	Route::post('hospital/store', [HospitalController::class, 'storeHospital']);
    //lista hospitais
    Route::get('list/hospitals', [HospitalController::class, 'listHospitals']);
    //cria usuarios hospital
    Route::post('hospital/store/user', [HospitalController::class, 'storeUserHospital']);

});




