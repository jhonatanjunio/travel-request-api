<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => __('messages.login_invalid'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->respondWithToken($token);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $token = auth('api')->login($user);

        return $this->respondWithToken($token, Response::HTTP_CREATED);
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'message' => __('messages.logout_success'),
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json(new UserResource(auth('api')->user()));
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken(string $token, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => new UserResource(auth('api')->user()),
        ], $status);
    }
}
