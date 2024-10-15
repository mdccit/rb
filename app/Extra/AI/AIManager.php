<?php

namespace App\Extra\AI;

use App\Extra\AI\Chat\ChatMessage;
use App\Extra\AI\Chat\ChatReply;
use App\Extra\AI\Exceptions\PromptFailedException;
use App\Extra\AI\PromptInfo\PromptInfoFactory;
use App\Extra\AI\Prompts\Prompt;
use App\Traits\HandlesHttpRetries;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIManager
{
    use HandlesHttpRetries;

    /**
     * Ask a question within an existing chat conversation.
     *
     * @throws \App\Extra\AI\Exceptions\PromptFailedException
     * @throws \App\Extra\AI\Exceptions\NoSuitableModelException
     */
    public function send(Prompt $prompt, array $options = []): ChatReply
    {
        $promptInfo = $this->makePromptInfoFactory()->calculate($prompt);

        $message = new ChatMessage('user', $promptInfo->prompt);

        $params = array_merge([
            'response_format' => [
                'type' => 'json_object'
            ],
            'temperature' => $prompt->getTemperature(),
            'model' => $promptInfo->model,
            'max_tokens' => $promptInfo->maxTokens,
            'messages' => [$message->toArray()],
        ], $options);

        try {
            $response = $this->client()->post('chat/completions', $params);
        } catch (RequestException $e) {
            throw new PromptFailedException(
                $promptInfo->model,
                $promptInfo->prompt,
                $this->extractError($e)
            );
        }

        $data = $response->json();

        $response = new ChatMessage(
            role: Arr::get($data, 'choices.0.message.role'),
            content: trim(Arr::get($data, 'choices.0.message.content')),
        );

        return new ChatReply(
            $message,
            $response,
            Arr::get($data, 'model'),
            Arr::get($data, 'usage.prompt_tokens'),
            Arr::get($data, 'usage.completion_tokens'),
        );
    }

    /**
     * Make a new HTTP API client.
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl('https://api.openai.com/v1/')
            ->withToken(config('openai.api_key'))
            ->retry(...$this->handleHttpRetry())
            ->timeout(5 * 60)
            ->throw();
    }

    /**
     * Extract the error message from the HTTP exception.
     */
    protected function extractError(RequestException $exception): string
    {
        return empty($exception->response->body())
            ? $exception->getMessage()
            : "{$exception->getCode()} ({$exception->response->json('error.message')})";
    }

    /**
     * Get an array of supported models and their respective token limits.
     *
     * @see https://help.openai.com/en/articles/4936856-what-are-tokens-and-how-to-count-them
     */
    public function getModels(): array
    {
        return $this->config['models'] ?? [];
    }

    /**
     * Make a new prompt info factory.
     */
    protected function makePromptInfoFactory(): PromptInfoFactory
    {
        return new PromptInfoFactory();
    }
}
