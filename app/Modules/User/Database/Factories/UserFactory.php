<?php

namespace App\Modules\User\Database\Factories;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    protected $model = User::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => FakerFactory::create()->name(),
            'email' => FakerFactory::create()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => FakerFactory::create()->unique()->numerify('+2010#######'),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
