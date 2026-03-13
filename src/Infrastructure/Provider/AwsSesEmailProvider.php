<?php

declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Domain\Notification\Provider\EmailProvider;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\Recipient;

final readonly class AwsSesEmailProvider implements EmailProvider
{
    public function __construct(
        private string $region,
        private string $fromEmail
    ) {
    }

    public function getName(): ProviderName
    {
        return ProviderName::AwsSes;
    }

    public function send(Recipient $recipient, MessageContent $content): bool
    {
        return true;
    }
}
