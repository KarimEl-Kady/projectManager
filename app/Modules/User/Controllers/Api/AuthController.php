<?php

namespace App\Modules\User\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\User\Requests\LoginRequest;
use App\Modules\User\Requests\RegisterRequest;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $service) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->service->register($request->validated());

        return response()->json([
            'message' => 'Account created successfully.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->validated());

        return response()->json([
            'message' => 'Logged in successfully.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request->user());

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
