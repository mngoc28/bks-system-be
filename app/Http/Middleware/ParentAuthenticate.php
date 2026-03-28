<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Enums\HttpStatus;
use App\Enums\StatusUser;
use Tymon\JWTAuth\Exceptions\JWTException;

class ParentAuthenticate
{
    use ApiResponser;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = auth()->guard('parent')->user();

            // Check if parent is not found
            if (!$user) {
                return $this->forbiddenResponse(
                    __('auth.unauthorized'),
                    HttpStatus::UNAUTHORIZED->value
                );
            }

            // Check if parent is blocked
            if ($user->status === StatusUser::BLOCKED->value) {
                return $this->forbiddenResponse(
                    __('auth.account_blocked'),
                    HttpStatus::UNAUTHORIZED->value
                );
            }
        } catch (JWTException $e) {
            return $this->errorResponse(
                __('auth.token_require'),
                HttpStatus::UNAUTHORIZED->value
            );
        }

        return $next($request);
    }
}
