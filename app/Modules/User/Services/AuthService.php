<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private UserRepository $users) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{user: User, token: string}
     */
    public function register(array $attributes): array
    {
        return DB::transaction(function () use ($attributes): array {
            $deviceName = (string) ($attributes['device_name'] ?? 'api');
            unset($attributes['device_name'], $attributes['password_confirmation']);

            /** @var User $user */
            $user = $this->users->create($attributes);

            return [
                'user' => $user,
                'token' => $user->createToken($deviceName)->plainTextToken,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $credentials
     * @return array{user: User, token: string}
     *
     * @throws AuthenticationException
     */
    public function login(array $credentials): array
    {
        $user = $this->users->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('The provided credentials are incorrect.');
        }

        return [
            'user' => $user,
            'token' => $user->createToken((string) ($credentials['device_name'] ?? 'api'))->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
