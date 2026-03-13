<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Domain\Notification\Service\RetryPolicy;

final readonly class ConfigurableRetryPolicy implements RetryPolicy
{
    public function __construct(
        private int $maxRetries = 5,
        private int $baseDelaySeconds = 60
    ) {
    }

    public function shouldRetry(int $attemptCount): bool
    {
        return $attemptCount < $this->maxRetries;
    }

    public function getRetryDelaySeconds(int $attemptCount): int
    {
        return $this->baseDelaySeconds * (2 ** $attemptCount);
    }

    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }
}
