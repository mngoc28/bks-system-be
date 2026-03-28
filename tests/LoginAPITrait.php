<?php

namespace Tests;

trait LoginAPITrait
{
    public function signInAPI()
    {
        /** @var TestResponse $response */
        $response = $this->postJson('api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue(isset($responseData['data']['authorisation']['token']));
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user',
                'authorisation' => [
                    'token',
                    'type',
                ]
            ]
        ]);
        return $responseData['data']['authorisation']['token'];
    }
}
