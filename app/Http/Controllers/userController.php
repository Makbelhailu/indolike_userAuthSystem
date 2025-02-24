<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function register(Request $request) {
        try {
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

            JWTAuth::login($user);

            return response()->json([
                "status" => true,
                "user" => $user,
                "message" => "user created Successfully, please login"
            ], 201);
        } catch(Exception $e) {
            return response()->json([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
        
    }

    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        try {
            if(!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    "status" => false,
                    "message" => "Invalid Credentials"
                ], 401);
            }

            $user = JWTAuth::user();

            $token = JWTAuth::fromUser($user);

            return response()->json([
                "status" => true,
                "user" => $user,
                "token" => $token
            ], 200);
        } catch(JWTException $e) {
            return response()->json([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function logout() {
        try {
            
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(["status" => true, "message" => "Logged out successfully"]);
        } catch(Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Failed to logout"
            ], 500);
        }
    }

    public function refresh(){
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
    
            return response()->json(["status" => true, "token" => $token]);
        } catch(Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Failed to refresh the token"
            ], 500);
        }
    }

    public function user() {

        try {
            $user = auth()->user();
            return response()->json(["status" => true, "user" => $user]);
        } catch(Exception $e) {
            return response()->json([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }
}
