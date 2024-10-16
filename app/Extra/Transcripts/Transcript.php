<?php

namespace App\Extra\Transcripts;

class Transcript
{
    /**
     * Constructor.
     */
    public function __construct(
        public readonly float $localGPA,
        public readonly float $americanGPA,
    ) {
    }
}
