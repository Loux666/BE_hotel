<?php

namespace App\Traits;

trait ApiResponser
{
    /**
     * Build a success response
     *
     * @param  string|array  $data
     * @param  string  $message
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'Success',
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $code);
    }

    /**
     * Build an error response
     *
     * @param  string  $message
     * @param  int  $code
     * @param  mixed  $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($message, $code, $errors = null)
    {
        return response()->json([
            'status' => 'Error',
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }
}
