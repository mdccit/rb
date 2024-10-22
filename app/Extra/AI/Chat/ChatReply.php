<?php

namespace App\Extra\AI\Chat;

class ChatReply
{
    /**
     * Constructor.
     */
    public function __construct(
        public readonly ChatMessage $question,
        public readonly ChatMessage $response,
        public readonly string $model,
        public readonly int $requestTokens,
        public readonly int $responseTokens,
    ) {
    }
}
