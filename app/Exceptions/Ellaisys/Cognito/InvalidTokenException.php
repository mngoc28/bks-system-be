<?php

namespace App\Exceptions\Ellaisys\Cognito;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class InvalidTokenException extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report($message = null)
    {
        $message = $message ?? __("messages.unauthorized_cognito");
        throw new AuthenticationException($message);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        /** @phpstan-ignore-next-line */
        return parent::render($request, $exception);
    }
}
