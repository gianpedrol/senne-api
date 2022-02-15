<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use JWTAuth;
use Hash;

use Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
  

        return response()->json([
          'user' => $user,
          'message' => 'parabéns você conseguiu!'
        ]);
        


    }
}
