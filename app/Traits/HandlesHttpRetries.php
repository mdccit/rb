<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\Client\RequestException;

trait HandlesHttpRetries
{
    /**
     * The number of times the HTTP request should be retried.
     */
    protected int $retryTimes = 3;

    /**
     * The number of milliseconds the HTTP request wait between retries.
     */
    protected int $retryMilliseconds = 2000;

    /**
     * Set the HTTP retry times.
     */
    public function setRetryTimes(int $times): static
    {
        $this->retryTimes = $times;

        return $this;
    }

    /**
     * Set the HTTP retry milliseconds.
     */
    public function setRetryMilliseconds(int $milliseconds): static
    {
        $this->retryMilliseconds = $milliseconds;

        return $this;
    }

    /**
     * Handle the HTTP client retry mechanism.
     */
    public function handleHttpRetry(): array
    {
        return [
            $this->retryTimes,
            $this->retryMilliseconds, // Milliseconds
            fn (Exception $exception) => (
                $exception instanceof RequestException
             && $exception->response->serverError()
            ),
        ];
    }
}
