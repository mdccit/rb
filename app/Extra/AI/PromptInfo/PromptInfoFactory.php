<?php

namespace App\Extra\AI\PromptInfo;

use App\Extra\AI\Exceptions\NoSuitableModelException;
use App\Extra\AI\Prompts\Prompt;
use App\Extra\AI\Tokenizer;

class PromptInfoFactory
{
    /**
     * Make a new prompt information instance suitable for chat completion requests.
     *
     * @throws NoSuitableModelException
     */
    public function calculate(Prompt $prompt): PromptInfo
    {
        $responseLimit = $prompt->getResponseLimit();

        return $this->findSuitableModel(
            $responseLimit,
            $prompt,
        );
    }

    /**
     * Find a suitable model based off a given prompt/response limit.
     * The completion type is used to retrieve the list of available models (chat/text).
     *
     * We can pass existingTokens to offset the tokens we need to account for in the prompt.
     * E.g. useful for chat, as we must factor in the tokens from the previous messages.
     *
     * @throws NoSuitableModelException
     */
    protected function findSuitableModel(int $responseLimit, Prompt $prompt, int $existingTokens = 0): PromptInfo
    {
        $models = $prompt->getAllowedModels();

        // We'll evaluate the props if we're using a prompt once here.
        // As this means we don't call it for each loop as it does not depend on the model.
        $promptProps = $prompt->props();

        foreach ($models as $model => $modelLimit) {
            // The model limit is the maximum amount of tokens we can use for *this* prompt.
            // The value is calculated by subtracting the response limit and the tokens already in-use
            // from the model's total limit.
            //
            // Example: 4096 limit - 500 in-use = 3596 usable
            $usableLimit = $modelLimit - $existingTokens;

            $promptContent = $prompt->render($model, $promptProps);

            if (empty($promptContent)) {
                continue;
            }

            $tokenCount = Tokenizer::count($promptContent);

            if ($tokenCount > $usableLimit) {
                continue;
            }

            return new PromptInfo(
                model: $model,
                prompt: $promptContent,
                promptTokens: $existingTokens + $tokenCount,
                maxTokens: $responseLimit,
            );
        }

        throw new NoSuitableModelException;
    }

    /**
     * Return the request-wide tokenizer instance.
     */
    protected function tokenizer(): Tokenizer
    {
        // TODO(Mason): Once we upgrade to laravel 11, wrap this in `once()`
        return new Tokenizer;
    }
}
