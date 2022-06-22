<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\sendEmailPasswordReset;
use App\Mail\emailPasswordReset;
use App\Models\User;
use App\Models\PasswordReset;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
//use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class NewPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $data = $request->email;

        $email = $request->email;
        $user = User::where('email', $email)->first();


        if ($user) {


            /*$url = Password::sendResetLink(
                $request->only('email')
            );*/

            try {
                $user->sendPasswordLink($user);
                /*
                User::where('email', $email)->sendPasswordLink(
                    $user,
                    $url
                );*/
            } catch (Exception $ex) {
                dd($ex);
                return response()->json(['error' => 'cannot be sended', $ex], 500);
            }

            /*if ($status == Password::RESET_LINK_SENT) {
                return [
                    'status' => __($status)
                ];
            }*/

            /*  throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);*/
        } else {
            return response()->json(['error' => "User Not found!"], 404);
        }
    }

    public function forgetPass(Request $request)
    {

        $user = User::where('email', $request->email)->first();


        $url = $user->sendPasswordLink($user);

        Mail::send(new emailPasswordReset($user, $url));


        return ['message', 'We have e-mailed your password reset link!'];
    }

    public function resetPassword(Request $request)
    {

        // dd($request->key);


        try {
            $decrypted = Crypt::decryptString($request->key);
        } catch (DecryptException $e) {
            //
        }

        $request->only('token', 'password', 'password_confirmation');


        $status = Password::reset(
            $request = ['email' => $decrypted, 'token' => $request->token, 'password' => $request->password, 'password_confirmation' => $request->password_confirmation],

            // dd($this->$user);
            function ($user) use ($request) {
                //$user->email = $decrypted;
                $user->forceFill([
                    'password' => Hash::make($request['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
                //dd($user);
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            User::where('email', $request['email'])->update(['status' => 1]);
            return response([
                'message' => 'Password reset successfully'
            ]);
        }

        return response([
            'message' => __($status)
        ], 500);
    }
}
