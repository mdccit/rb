<?php

namespace App\Extra\Transcripts\Exceptions;

class CouldNotCalculateGPAException extends TranscriptException
{
    /**
     * Constructor.
     */
    public function __construct(
        public readonly string $type
    )
    {
        parent::__construct(
            "The [$type] GPA could not be calculated by AI.",
        );
    }
}
