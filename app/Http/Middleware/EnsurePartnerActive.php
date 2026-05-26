<?php

namespace App\Http\Middleware;

use App\Enums\Status;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePartnerActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user();

        if ($user && $user->role === 'partner' && (int) $user->status !== Status::ACTIVE->value) {
            // Allow list of routes accessible during onboarding
            $allowedPaths = [
                'partner/auth/logout',
                'partner/business-profile',
                'partner/auth/submit-onboarding',
                'partner/auth/resubmit-onboarding',
                'partner/auth/sign-contract',
                'partner/user/profile',
            ];

            $currentPath = $request->path();

            $isAllowed = false;
            foreach ($allowedPaths as $allowed) {
                if (str_contains($currentPath, $allowed) || $request->is($allowed)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tài khoản đối tác chưa được phê duyệt. Vui lòng hoàn tất các bước đăng ký hồ sơ.',
                    'partner_status' => (int) $user->status
                ], 403);
            }
        }

        return $next($request);
    }
}
