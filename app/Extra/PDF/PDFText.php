<?php

namespace App\Extra\PDF;

class PDFText
{
    /**
     * Constructor.
     */
    public function __construct(
        public readonly string $text,
        public readonly bool $ocr
    ) {
    }
}
