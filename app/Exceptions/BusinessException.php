<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;
use Illuminate\Support\Facades\Lang;

class BusinessException extends Exception
{
    use ApiResponser;

    public function render($request)
    {
        $errorCode = $this->getMessage();
        $message = Lang::get("messages." . $errorCode);
        return $this->errorResponse($message, $errorCode);
    }
}
