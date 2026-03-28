<?php

namespace App\Traits;

use App\Enums\HttpStatus;

trait ApiResponser
{
    /**
     * Summary of successResponse
     * @param mixed $data
     * @param mixed $message
     * @param mixed $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(
        $data,
        $message = null,
        $code = HttpStatus::OK
    ) {
        return response()->json(
            [
                "status"  => "success",
                "message" => $message,
                "data"    => $data,
            ],
            $code->value
        );
    }

    /**
     * Summary of createdResponse
     * @param mixed $data
     * @param mixed $message
     * @param mixed $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createdResponse(
        $data,
        $message = null,
        $code = HttpStatus::CREATED
    ) {
        return response()->json(
            [
                "status"  => "success",
                "message" => $message,
                "data"    => $data,
            ],
            $code->value
        );
    }

    /**
     * Summary of errorResponse
     * @param mixed $message
     * @param mixed $err_code
     * @param HttpStatus $code
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(
        $message = null,
        $err_code = null,
        $code = HttpStatus::BAD_REQUEST,
        $data = null
    ) {
        return response()->json(
            [
                "status"  => "error",
                "message" => $message,
                "code"    => $err_code,
                "data"    => $data,
            ],
            $code->value
        );
    }

    /**
     * Summary of validateError
     * @param mixed $message
     * @param mixed $err_code
     * @param HttpStatus $code
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    // 422 validation response
    protected function validateError(
        $message = null,
        $err_code = null,
        HttpStatus $code = HttpStatus::VALIDATION_ERROR,
        $data = null
    ) {
        return response()->json(
            [
                "status" => "error",
                "errors" => $message,
                "code"   => $err_code,
            ],
            $code->value
        );
    }

    /**
     * Summary of forbiddenResponse
     * @param mixed $message
     * @param mixed $err_code
     * @param HttpStatus $code
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    // 403 forbidden response
    protected function forbiddenResponse(
        $message = null,
        $err_code = null,
        HttpStatus $code = HttpStatus::FORBIDDEN,
        $data = null
    ) {
        return response()->json(
            [
                "status"  => "error",
                "message" => $message,
                "code"    => $err_code,
                "data"    => $data,
            ],
            $code->value
        );
    }
}
