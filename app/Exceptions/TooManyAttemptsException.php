<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Middleware\ThrottleRequests;

class TooManyAttemptsException extends ThrottleRequests
{
    protected function buildException(
        $request,
        $key,
        $maxAttempts,
        $responseCallback = null
    ) {
        $retryAfter = $this->getTimeUntilNextRetry($key);

        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );
        return is_callable($responseCallback)
            ? new HttpResponseException($responseCallback($request, $headers))
            : new ThrottleRequestsException(
                __("messages.TOO_MANY_ATTEMPTS"),
                null,
                $headers
            );
    }
}
