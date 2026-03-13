<?php

declare(strict_types=1);

namespace App\Domain\Notification\Service;

interface RetryPolicy
{
    public function shouldRetry(int $attemptCount): bool;

    public function getRetryDelaySeconds(int $attemptCount): int;

    public function getMaxRetries(): int;
}
