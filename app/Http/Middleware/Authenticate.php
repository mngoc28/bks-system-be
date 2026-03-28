<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        /*
            |--------------------------------------------------------------------------
            | Handle unauthenticated requests
            |--------------------------------------------------------------------------
            |
            | For API requests, return a JSON response with an error message and a 401 status code.
            | For web requests, return null to prevent redirecting.
            |
            */
        if (! $request->bearerToken()) {
            abort(response()->json(
                [
                    'success' => 'error',
                    'message' => __('auth.unauthenticated'),
                    'data'    => null,
                ],
                401
            ));
        }
        return null;
    }
}
