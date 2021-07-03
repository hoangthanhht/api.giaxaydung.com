<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use App\Http\Resources\JsonResponse;
use Log;
use Illuminate\Http\Response;

abstract class AbstractException extends Exception
{
    protected $code;
    protected $message = [];

    public function __construct($message = null, $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $this->code = $code;
        $this->message = $message ?: 'Server Exception';

        parent::__construct($message, $code);
    }

    public function render(Request $request)
    {
        $json = [
            'code' => $this->code,
            'message' => [$this->message],
            'data' => null,
        ];

        return new JsonResponse($json);
    }

    public function report()
    {
        Log::emergency($this->message);
    }
}