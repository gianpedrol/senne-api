<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class NewPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);


        $email = $request->email;
        $user = User::where('email', $email)->first();


        if ($user) {

            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status == Password::RESET_LINK_SENT) {
                return [
                    'status' => __($status)
                ];
            }

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        } else {
            return response()->json(['error' => "User Not found!"], 404);
        }
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
