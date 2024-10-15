<?php

namespace App\Extra\AI\PromptInfo;

class PromptInfo
{
    /**
     * Constructor.
     */
    public function __construct(
        public readonly string $model,
        public readonly string $prompt,
        public readonly int $promptTokens,
        public readonly int $maxTokens,
    ) {
    }
}
