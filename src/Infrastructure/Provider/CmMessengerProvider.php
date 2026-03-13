<?php

declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Domain\Notification\Provider\MessengerProvider;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\Recipient;

final readonly class CmMessengerProvider implements MessengerProvider
{
    public function __construct(
        private string $apiKey,
        private string $productToken
    ) {
    }

    public function getName(): ProviderName
    {
        return ProviderName::CmMessenger;
    }

    public function send(Recipient $recipient, MessageContent $content): bool
    {
        return true;
    }
}
