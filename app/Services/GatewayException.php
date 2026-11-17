<?php

namespace App\Services;

use RuntimeException;

class GatewayException extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $httpStatus = null)
    {
        parent::__construct($message);
    }
}
