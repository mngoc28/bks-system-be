<?php

namespace App\Http\Middleware;

use App\Enums\AdminRole;
use App\Enums\HttpStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if admin is authenticated
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'message' => __('auth.unauthorized'),
                'status' => HttpStatus::UNAUTHORIZED->value,
            ], HttpStatus::UNAUTHORIZED->value);
        }

        //
        $admin = Auth::guard('admin')->user();

        // Check if admin is super admin
        if ($admin->role_id !== AdminRole::SUPER_ADMIN->value) {
            return response()->json([
                'message' => __('admin.unauthorized'),
                'status' => HttpStatus::UNAUTHORIZED->value,
            ], HttpStatus::UNAUTHORIZED->value);
        }
        return $next($request);
    }
}
