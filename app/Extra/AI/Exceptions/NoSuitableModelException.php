<?php

namespace App\Extra\AI\Exceptions;

class NoSuitableModelException extends AIException
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            "No suitable model could be found to satisfy token requirements.",
        );
    }
}
