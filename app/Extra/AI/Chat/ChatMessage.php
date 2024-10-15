<?php

namespace App\Extra\AI\Chat;

use App\Extra\AI\Chat\Contracts\Message;
use Illuminate\Contracts\Support\Arrayable;

class ChatMessage implements Arrayable
{
    /**
     * Constructor.
     */
    public function __construct(
        public readonly string $role,
        public readonly string $content
    ) {
    }

    /**
     * Attempt to decode the chat message as JSON.
     */
    public function json(): array
    {
        return (array) json_decode($this->content, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Format the chat message in a way OpenAI & Azure can understand.
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }

    /**
     * Format the chat message into a human-readable string
     */
    public function toHuman(): string
    {
        return "[$this->role] $this->content";
    }
}
