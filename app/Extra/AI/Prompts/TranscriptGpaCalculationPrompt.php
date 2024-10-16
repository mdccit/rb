<?php

namespace App\Extra\AI\Prompts;

class TranscriptGpaCalculationPrompt extends Prompt
{
    /**
     * The name of the prompt.
     * Used to reference the prompt's blade view.
     */
    protected static string $name = 'transcript-gpa-calculation';

    /**
     * Constructor.
     */
    public function __construct(
        protected string $documentText,
        protected bool $ocr,
        protected string $originCountry,
        protected string $language,
    ) {
    }

    /**
     * Get the props to render in the template.
     */
    public function props(): array
    {
        return [
            'documentText' => $this->documentText,
            'ocr' => $this->ocr,
            'originCountry' => $this->originCountry,
            'language' => $this->language,
        ];
    }
}
