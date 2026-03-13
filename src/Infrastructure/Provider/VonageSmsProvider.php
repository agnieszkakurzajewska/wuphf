<?php

declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Domain\Notification\Provider\SmsProvider;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\Recipient;

final readonly class VonageSmsProvider implements SmsProvider
{
    public function __construct(
        private string $apiKey,
        private string $apiSecret,
        private string $fromNumber
    ) {
    }

    public function getName(): ProviderName
    {
        return ProviderName::Vonage;
    }

    public function send(Recipient $recipient, MessageContent $content): bool
    {
        return true;
    }
}
