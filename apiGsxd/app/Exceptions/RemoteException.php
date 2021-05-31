<?php

namespace App\Exceptions;

use Illuminate\Http\Response;

class RemoteException extends AbstractException
{
    public function __construct($message = '', $code = null)
    {
        if (!$message) {
            $message = __('exception.remote_error');
        }

        if (!$code) {
            $code = Response::HTTP_FORBIDDEN;
        }
        parent::__construct($message, $code);
    }
}