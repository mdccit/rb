<?php

namespace App\Extra\AI\Exceptions;

use Exception;

class AIException extends Exception
{
    /**
     * Constructor.
     */
    public function __construct(
        string $message
    ) {
        parent::__construct($message);
    }
}
