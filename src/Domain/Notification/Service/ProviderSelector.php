<?php

declare(strict_types=1);

namespace App\Domain\Notification\Service;

use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\ProviderName;

interface ProviderSelector
{
    public function selectProvider(Channel $channel, array $failedProviders): ?ProviderName;
}
