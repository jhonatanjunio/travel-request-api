<?php

namespace App\Exceptions\TravelRequest;

use Exception;

class TravelRequestNotFoundException extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct(__('messages.travel_request_not_found', ['id' => $id]));
    }
}
