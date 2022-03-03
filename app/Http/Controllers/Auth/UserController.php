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

class UserController extends Controller
{

    public function update(Request $request)
    {
        $id = $request->id;
        $data = $request->all();

        try {
            $user = User::findOrFail($id)->update($data);
        }catch (\Exception $e){
            return response()->json(['message'=>'Fail on update a user'],400);
        }

        return response()->json(['message'=>'user updated']);

    }

    public function delete(Request $request){
        $id = $request->id;

        try {
            $user = User::findOrFail($id)->delete();
        }catch (\Exception $e){
            return response()->json(['message'=>'Fail on delete a user'],400);
        }

    }

    public function sendResetPassword(Request $request){

        $frontUrl = env('FRONTEND_URL');
        $frontRoute= env('FRONTEND_RESET_PASSWORD_URL');

        $email = $request->get('email');
        $user = User::where('email',$email)->get();


        if( count($user) > 0){
            $urlTemp = $frontUrl . $frontRoute. URL::temporarySignedRoute(
                    'verifyResetRoute', now()->addMinutes(30), ['user' => $user[0]['id']]
                );

            sendEmailPasswordReset::dispatch($user[0],$urlTemp);

            return response()->json(['message'=>'email reset password send']);

        }else{
            return response()->json(['message'=>'User not found'],404);
        }
    } 

    public function verifyResetRoute(Request $request){

        if (! $request->hasValidSignature()) {
            abort(401);
        }

        return response()->json(['message'=>'valid url']);
    }

    public function reset(Request $request){
        $id = $request->id;
        $password = Hash::make($request->get('password'));

        try {
            User::findOrFail($id)->update(['password'=>$password]);
        }catch (\Exception $e){
            return response()->json(['message'=>'Fail to reset password'],400);
        }

        return response()->json(['message'=>'Password reset successful']);
    }

    public function verification(Request $request){
        $user_id = $request->route('user');

        if (!$request->hasValidSignature()) {
            abort(401);
        }

        try {
            $user = User::find($user_id);

            $user->markEmailAsVerified();

            return response()->json(['message'=>'verified user email']);
        }catch (\Exception $e){
            return response()->json(['message'=>'erro on try to validade user'],400);
        }
    }

    public function resend(Request $request){
        $id = $request->get('id');

        $frontUrl = env('FRONTEND_URL');
        $frontRoute= env('FRONTEND_EMAIL_VERIFY_URL');

        $user = User::find($id);

        if($user){
            $urlTemp = $frontUrl . $frontRoute. URL::temporarySignedRoute(
                    'verification', now()->addMinutes(30), ['user' => $user->id]
                );

            sendEmailVerification::dispatch($user,$urlTemp);
        }else{
            response()->json(['message'=>'User not found'],404);
        }

    }


}
