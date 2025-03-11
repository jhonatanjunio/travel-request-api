<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\UserRegistrationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw new InvalidCredentialsException("E-mail ou senha incorretos");
            }

            $token = $user->createToken('api-token');

            return response()->json([
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60,
                'user' => new UserResource($user)
            ], 200);
        } catch (InvalidCredentialsException $e) {
            return $e->render($request);
        }
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('api-token');

            DB::commit();

            return response()->json([
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60,
                'user' => new UserResource($user)
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw new UserRegistrationException($e->errors(), "Erro na validação dos dados");
        } catch (\Exception $e) {
            DB::rollBack();
            throw new UserRegistrationException([], "Erro ao registrar usuário: " . $e->getMessage());
        }
    }

    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

    public function me(): JsonResponse
    {
        return response()->json(new UserResource(auth()->user()));
    }
}
