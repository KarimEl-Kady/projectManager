<?php

namespace App\Modules\User\Tests\Feature;

use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_a_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Karim User',
            'email' => 'KARIM@example.com',
            'phone' => '+201001234567',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'device_name' => 'postman',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'karim@example.com')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['user' => ['uuid', 'name', 'email', 'phone'], 'token']]);

        $user = User::query()->where('email', 'karim@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_registration_validation_rejects_duplicate_contact_data(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/register', [
            'name' => 'Duplicate User',
            'email' => $user->email,
            'phone' => $user->phone,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'phone']);
    }

    public function test_user_can_login_view_profile_and_logout_current_token(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
            'device_name' => 'tests',
        ])->assertOk();

        $token = $login->json('data.token');

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.uuid', $user->uuid);

        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_credentials_and_unauthenticated_access_return_401(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized();

        $this->getJson('/api/projects')->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_sanctum_routes(): void
    {
        Sanctum::actingAs($user = User::factory()->create());

        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.uuid', $user->uuid);
    }
}
