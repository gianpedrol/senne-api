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
    public function createPassword(Request $request)
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
            return response()->json(['message' => "User Not found!"], 404);
        }
    }

    /**
     * @OA\Post(
     * path="/api/password/forgot",
     * operationId="ResetPassword",
     * tags={"ResetPassword"},
     * summary="Reset Password",
     * description="Reset Password",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="email", type="email")
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Mail sended",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=200,
     *          description="Mail sended",
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
    public function forgotPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
        ]);

        $data = $request->email;

        $email = $request->email;
        $user = User::where('email', $email)->first();


        if ($user) {

            $user->sendPasswordLink($user);
            $url = $user->sendPasswordLink($user);

            try {
                Mail::to($request->email)->send(new emailPasswordReset($user, $url));
                return response()->json(['message' => "Email Enviado"], 200);
            } catch (Exception $ex) {
                return response()->json(['message' => 'Não foi possível enviar o email', $ex], 500);
            }
        } else {
            return response()->json(['message' => "Usuário não encontrado!"], 404);
        }
    }


    public function resetPassword(Request $request)
    {

        if ($request->status == 1) {
            try {
                $decrypted = Crypt::decryptString($request->key);
            } catch (DecryptException $e) {
                //
            }

            $request->only('token', 'password', 'password_confirmation');

            $userEmail = User::where('email', $decrypted)->first();
            if (empty($userEmail)) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $status = Password::reset(
                $request = ['email' => $decrypted, 'token' => $request->token, 'password' => $request->password, 'password_confirmation' => $request->password_confirmation],
                function ($user) use ($request) {
                    //$user->email = $decrypted;
                    $user->forceFill([
                        'password' => Hash::make($request['password']),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset([$user]));

                    $user->tokens()->delete();
                }
            );

            if ($status == Password::PASSWORD_RESET) {
                User::where('email', $request['email'])->update(['status' => 1]);
                return response([
                    'message' => 'Senha cadastrada com sucesso!'
                ]);
            }

            return response([
                'message' => __($status)
            ], 500);
        } else if ($request->status == 2) {
            try {
                $decrypted = Crypt::decryptString($request->key);
            } catch (DecryptException $e) {
                //
            }

            $request->only('token', 'password', 'password_confirmation');

            $updatePassword = DB::table('password_resets')
                ->where([
                    'email' =>  $decrypted,
                    'token' => $request->token
                ])
                ->first();

            if (!$updatePassword) {
                return response()->json(['message' => 'Invalid token!'], 404);
            }
            $user = User::where('email', $decrypted)->first();

            if ($user->status != 1) {
                return response()->json(['message' => 'Não autorizado, vocês está inativo'], 400);
            }

            $user = User::where('email', $decrypted)
                ->update(['password' => Hash::make($request->password)]);

            $tokensExpired =  PasswordReset::where('email',  $decrypted)->get();
            //dd($tokensExpired);

            if ($user) {
                foreach ($tokensExpired as $token) {
                    $passwordEmail = [
                        'email' => $token->email
                    ];
                    PasswordReset::where('email', $passwordEmail['email'])->delete();
                }
                //DB::table('password_resets')->where(['email' => $request->email])->delete();
                return (['message', 'Sua senha foi alterada!', 'user' => $user]);
            }
        }
    }
}
