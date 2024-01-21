<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client;

class UserController extends Controller
{
    public function signUp(SignUpRequest $request)
    {
        $user = User::create([
                "name" => $request->input('name'),
                "email" => $request->input('email'),
                "password" => Hash::make($request->input('password')),
            ]);

        $token = $user->createToken('Token')->accessToken;

        return response()->json([ 'name' => $user->name ], 200, ['authorization' => $token, 'Access-Control-Expose-Headers' => 'authorization']);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if (is_null($user) || Hash::check($request->input('password'), $user->password) === false) {
            throw new BadRequestHttpException('メールアドレス又はパスワードが間違っています');
        }

        $token = $user->createToken('Token')->accessToken;

        return response()->json([], 200, ['authorization' => $token, 'Access-Control-Expose-Headers' => 'authorization']);
    }
}
