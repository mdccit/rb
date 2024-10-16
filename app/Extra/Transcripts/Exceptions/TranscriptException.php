<?php

namespace App\Extra\Transcripts\Exceptions;

use Exception;

class TranscriptException extends Exception
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
