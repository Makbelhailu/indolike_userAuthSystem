<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function register(Request $request) {
        $validated = Validator($request->all(), [
            "username" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:6|max:16|confirmed"
        ]);

        if($validated->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validated->errors()
            ], 400);
        }

        $user = User::create([
            "username" => $request->get("username"),
            "email" => $request->get("email"),
            "password" => Hash::make($request->get("password"))
        ]);

        Auth::login($user);

        return response()->json([
            "status" => true,
            "user" => $user,
            "message" => "user created Successfully, please login"
        ], 201);
    }

    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        try {
            if(!$token = Auth::attempt($credentials)) {
                return response()->json([
                    "status" => false,
                    "message" => "Invalid Credentials"
                ], 401);
            }

            $user = Auth::user();

            $token = Auth::fromUser($user);

            return response()->json([
                "status" => true,
                "user" => $user,
                "token" => $token
            ], 200);
        } catch(JWTException $e) {
            return response()->json([
                "status" => false,
                "message" => "Could not create token"
            ], 500);
        }
    }
}
