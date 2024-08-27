<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function register (RegisterRequest $registerRequest)
    {
        $user = User::create([
            'email' => $registerRequest->input('email'),
            'password' => Hash::make($registerRequest->input('password'))
        ]);

        return Response::successResponse('You are Registered Successfully', $user);
    }

    public function login (LoginRequest $loginRequest)
    {
        $email = $loginRequest->input('email');
        $password = $loginRequest->input('password');

        if (Auth::attempt(['email' => $email, 'password' => $password])) {

            $user = User::find(Auth::user()->id);
            $token = $user->createToken('appAuthenticationToken')->accessToken;

            $responseData = [
                'user' => $user,
                'token' => $token
            ];

            return Response::successResponse('You are Logged in!', $responseData);
        }

        return Response::errorResponse('Email or Password is Wrong!', [], 401);
    }

    public function logout (Request $request)
    {
        if (Auth::user()) {
            $request->user()->token()->revoke();

            return Response::successResponse('You are Logged out Successfully!', []);
        }
    }
}
