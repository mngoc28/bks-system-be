<?php

namespace App\Exceptions;

use App\Enums\HttpStatus;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        "current_password",
        "password",
        "password_confirmation",
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Summary of render
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($exception instanceof TokenInvalidException) {
                return $this->jsonError(__('auth.token_invalid'), HttpStatus::UNAUTHORIZED);
            }

            if ($exception instanceof TokenExpiredException) {
                return $this->jsonError(__('auth.token_expired'), HttpStatus::UNAUTHORIZED);
            }

            if ($exception instanceof JWTException) {
                return $this->jsonError(__('auth.token_required'), HttpStatus::UNAUTHORIZED);
            }

            if ($exception instanceof AuthenticationException) {
                return $this->jsonError(__('auth.unauthenticated'), HttpStatus::UNAUTHORIZED);
            }

            if ($exception instanceof RouteNotFoundException) {
                return $this->jsonError(__('auth.route_not_found'), HttpStatus::NOT_FOUND);
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.invalid_data'),
                    'errors'  => $exception->errors(),
                ], HttpStatus::VALIDATION_ERROR->value);
            }

            return $this->jsonError(
                $exception->getMessage() ?: __('auth.general_error'),
                HttpStatus::INTERNAL_SERVER_ERROR
            );
        }

        return parent::render($request, $exception);
    }

    /**
     * Summary of jsonError
     * @param string $message
     * @param HttpStatus $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    private function jsonError(string $message, HttpStatus $statusCode)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ], $statusCode->value);
    }
}
