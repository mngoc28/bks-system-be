<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\LoginAPITrait;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use DatabaseMigrations;
    use LoginAPITrait;

    public function setUp(): void
    {
        parent::setUp();
        User::query()->delete();
        // seed the database
        $this->artisan('migrate:refresh');
        // $this->artisan('db:seed');
//        User::factory(1)->create();
        // alternatively you can call
        $this->seed();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_can_register()
    {
        $formData = [
            'name' => 'test',
            'email' => 'a@a.test',
            'password' => '123456',
            'phone' => '12345678'
        ];
        $response = $this->post(route('auth.register'), $formData);

        $response->assertStatus(201);
    }

    public function test_can_get_user()
    {
        $token = $this->signInAPI();
        $response = $this->getJson( 'api/user',
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'name',
                'email'
            ]
        ]);
    }
}
