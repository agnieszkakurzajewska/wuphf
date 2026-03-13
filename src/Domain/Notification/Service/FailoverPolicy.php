<?php

declare(strict_types=1);

namespace App\Domain\Notification\Service;

use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\ProviderName;

interface FailoverPolicy
{
    public function getProvidersForChannel(Channel $channel): array;

    public function getNextProvider(Channel $channel, array $failedProviders): ?ProviderName;
}
