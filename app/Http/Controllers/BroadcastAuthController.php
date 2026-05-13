<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/**
 * Custom auth endpoint cho WebSocket private/presence channels.
 *
 * Khác với `/broadcasting/auth` mặc định của Laravel (đọc user từ session
 * cookie), endpoint này được route qua middleware `jwt.auth`, xác thực user
 * bằng JWT Bearer token và tận dụng `Broadcast::auth()` để delegate cho
 * Pusher broadcaster sinh signature theo channel callbacks định nghĩa trong
 * `routes/channels.php`.
 */
class BroadcastAuthController extends Controller
{
    /**
     * Authenticate the request for channel access.
     *
     * @return mixed Pusher signed payload hoặc 401/403 response.
     */
    public function authenticate(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($user === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Đảm bảo Broadcast::auth() đọc đúng user (Auth helper) khi guard 'api' đã set user.
        Auth::shouldUse('api');

        try {
            return Broadcast::auth($request);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            Log::info('broadcast_auth_denied', [
                'user_id' => $user->id,
                'channel' => $request->input('channel_name'),
            ]);

            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        } catch (\Throwable $e) {
            Log::warning('broadcast_auth_error', [
                'user_id' => $user->id,
                'channel' => $request->input('channel_name'),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Channel authorization failed.',
            ], 403);
        }
    }
}
