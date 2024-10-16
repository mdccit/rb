<?php

namespace App\Extra\AI\Chat;

use Illuminate\Contracts\Support\Arrayable;

class ChatConversation implements Arrayable
{
    /**
     * The sent and received messages.
     *
     * @var ChatMessage[]
     */
    protected array $messages = [];

    /**
     * Constructor.
     */
    public function __construct(
        protected readonly string $driver,
        protected readonly array $options = []
    ) {
        // If a system message is supplied, we will prepend it
        // to the conversation, so it is provided as context.
        if (isset($options['system-message'])) {
            $this->messages[] = new ChatMessage('system', $options['system-message']);
        }
    }

    /**
     * Get all messages in the conversation.
     *
     * @return ChatMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Add a message to the conversation.
     */
    public function addMessage(ChatMessage $message): static
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Get the last message sent by the assistant.
     */
    public function getLastReply(): ChatMessage
    {
        return collect($this->messages)
            ->filter(fn ($message) => $message->role === 'assistant')
            ->last();
    }

    /**
     * Transform the conversation into an array.
     */
    public function toArray(): array
    {
        return collect($this->messages)->toArray();
    }

    /**
     * Transform the conversation into a human-readable string.
     */
    public function toHuman(): string
    {
        return collect($this->messages)
            ->map(fn (ChatMessage $message) => $message->toHuman())
            ->implode(PHP_EOL);
    }
}
