<?php

require __DIR__ . '/public/index.php';

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

// Get a valid token for user 2 (partner)
$user = User::find(2);
$token = JWTAuth::fromUser($user);
echo "JWT Token generated successfully: " . substr($token, 0, 15) . "...\n\n";

function simulateRequest($uri, $token)
{
    echo "Simulating GET $uri...\n";
    $request = Request::create('/api/v1/' . $uri, 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $request->headers->set('Accept', 'application/json');

    $kernel = app(\Illuminate\Contracts\Http\Kernel::class);

    $start = microtime(true);
    $response = $kernel->handle($request);
    $end = microtime(true);

    echo "Status code: " . $response->getStatusCode() . "\n";
    echo "Time: " . round(($end - $start) * 1000, 2) . " ms\n";
    echo "Content preview: " . substr($response->getContent(), 0, 200) . "\n";
    echo "----------------------------------------\n\n";

    $kernel->terminate($request, $response);
}

simulateRequest('partner/user/profile', $token);
simulateRequest('partner/properties/types', $token);
simulateRequest('partner/properties/searchAll?page=1&per_page=5', $token);
simulateRequest('partner/notifications?page=1', $token);
simulateRequest('partner/cancellation-requests?status=pending', $token);

function simulatePostRequest($uri, $token, $data)
{
    echo "Simulating POST $uri with data " . json_encode($data) . "...\n";
    $request = Request::create('/api/v1/' . $uri, 'POST', $data);
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $request->headers->set('Accept', 'application/json');

    $kernel = app(\Illuminate\Contracts\Http\Kernel::class);

    $start = microtime(true);
    $response = $kernel->handle($request);
    $end = microtime(true);

    echo "Status code: " . $response->getStatusCode() . "\n";
    echo "Time: " . round(($end - $start) * 1000, 2) . " ms\n";
    echo "Content preview: " . substr($response->getContent(), 0, 200) . "\n";
    echo "----------------------------------------\n\n";

    $kernel->terminate($request, $response);
}

simulatePostRequest('broadcasting/auth', $token, [
    'channel_name' => 'private-partner.2',
    'socket_id' => '1234.5678'
]);
