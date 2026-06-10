<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserNotFoundAuthTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_deleted_user_token_returns_401(): void
    {
        $user = User::create([
            'email' => 'temp_user@gmail.com',
            'password' => bcrypt('123456a!'),
            'role' => 'user',
            'name' => 'Temp User',
        ]);

        $token = JWTAuth::fromUser($user);

        // Delete the user from database
        $user->delete();

        $response = $this->getJson('/api/v1/stay/notifications', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Let's dump the response to see what it is
        $response->dump();

        $response->assertStatus(401);
    }
}
