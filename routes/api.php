<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

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





