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

class NewPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);


        $user = User::where('email', $request->email)->first();

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

        $rules = [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
            return $array;
        }

        $status = Password::reset(
            $request->only('email', 'token', 'password', 'password_confirmation'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );


        if ($status == Password::PASSWORD_RESET) {
            return response([
                'message' => 'Password reset successfully'
            ]);
        }

        return response([
            'message' => __($status)
        ], 500);
    }
}
