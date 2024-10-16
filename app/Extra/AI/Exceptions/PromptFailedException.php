<?php

namespace App\Extra\AI\Exceptions;

class PromptFailedException extends AIException
{
    /**
     * Constructor.
     */
    public function __construct(
        protected string $model,
        protected string $prompt,
        protected string $response
    ) {
        parent::__construct(
            "Prompt failed. Received error message [$response].",
        );
    }

    /**
     * Get the language model used.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Get the prompt sent.
     */
    public function getPrompt(): string
    {
        return $this->prompt;
    }

    /**
     * Get the error response received.
     */
    public function getResponse(): string
    {
        return $this->response;
    }
}
