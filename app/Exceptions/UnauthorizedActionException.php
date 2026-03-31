<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedActionException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('messages.unauthorized_action'));
    }
}
