<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        /*
        * @var User $user
        */
        $user = Auth::user();
        
        $user->tokens()->delete();
        
        $tokenResult = $user->createToken('auth-token', ['*'], now()->addHours(3));
        $token = $tokenResult->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->getKey(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                ],
                'access_token' => $tokenResult->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->expires_at->toISOString()
            ]
        ]);
    }


    public function logout(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true, 
            'message' => 'Successfully logged out'
        ]);
    }

    public function me(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->getKey(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    public function refresh(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        
        $user->currentAccessToken()->delete();
        
        $tokenResult = $user->createToken('auth-token', ['*'], now()->addHours(3));
        $token = $tokenResult->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'user' => [
                    'id' => $user->getKey(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                ],
                'access_token' => $tokenResult->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->expires_at->toISOString()
            ]
        ]);
    }
}
