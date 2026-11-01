<?php

namespace App\Services;

use Throwable;

class ServerProvisioningException extends \RuntimeException
{
    public function __construct(public readonly string $serverId, string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
