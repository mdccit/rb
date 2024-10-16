<?php

namespace App\Extra\AI\Prompts;

use App\Support\Traits\Nameable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

abstract class Prompt
{
    /**
     * Get the allowed token count for the AI response.
     */
    protected int $responseLimit = 256;

    /**
     * The temperature setting for responses.
     *
     * https://gptforwork.com/guides/openai-gpt3-temperature
     */
    protected int|float $temperature = 0;

    /**
     * Get the human-readable title of the prompt.
     */
    public static function name(): string
    {
        return property_exists(static::class, 'name')
            ? static::$name
            : Str::kebab(class_basename(static::class));
    }

    /**
     * Set the token response limit.
     */
    public function setResponseLimit(int $limit): static
    {
        $this->responseLimit = $limit;

        return $this;
    }

    /**
     * Get the response limit token count for the text generation response.
     */
    public function getResponseLimit(): int
    {
        return $this->responseLimit;
    }

    /**
     * Set the temperature setting for the prompt.
     */
    public function setTemperature(int|float $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * Get the temperature of the text generation response.
     */
    public function getTemperature(): int|float
    {
        return $this->temperature;
    }

    /**
     * Get the allowed models the prompt should be used with.
     *
     * @return array<string>
     */
    public function getAllowedModels(): array
    {
        return config('openai.models');
    }

    /**
     * Get the props to render in the template.
     */
    abstract public function props(): array;

    /**
     * Render the template for the prompt.
     */
    public function render(string $model, array $props = []): string
    {
        $rendered = Blade::render(
            sprintf('ai.prompts.%s', static::name()),
            array_merge(['model' => $model], $props),
        );


        return collect(explode(PHP_EOL, $rendered))
        ->map(fn (string $line) => trim($line))
        ->implode(PHP_EOL);
    }
}
