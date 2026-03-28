<?php

namespace Tests\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait AuthenticationRequest
{
    private $password;

    protected function withUser($email = null): self
    {
        $email = $email ?: 'user@example.com';
        $credentials = [
            "email" => $email,
            "password" => $this->getPassword()
        ];

        $token = Auth::attempt($credentials);

        return $this->withToken($token);
    }

    public function getPassword(): string
    {
        return $this->password ?? 'password123';
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
