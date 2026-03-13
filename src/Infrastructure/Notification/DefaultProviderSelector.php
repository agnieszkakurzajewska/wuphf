<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Domain\Notification\Service\ProviderSelector;
use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\ProviderName;

final readonly class DefaultProviderSelector implements ProviderSelector
{
    public function __construct(
        private FailoverPolicy $failoverPolicy
    ) {
    }

    public function selectProvider(Channel $channel, array $failedProviders): ?ProviderName
    {
        if (empty($failedProviders)) {
            $providers = $this->failoverPolicy->getProvidersForChannel($channel);
            $first = $providers[0] ?? null;
            return $first instanceof ProviderName ? $first : ($first ? ProviderName::from($first) : null);
        }

        return $this->failoverPolicy->getNextProvider($channel, $failedProviders);
    }
}
