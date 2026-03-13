<?php

declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Domain\Notification\Provider\PushProvider;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\Recipient;

final readonly class FcmPushProvider implements PushProvider
{
    public function __construct(
        private string $projectId,
        private string $credentialsPath
    ) {
    }

    public function getName(): ProviderName
    {
        return ProviderName::Fcm;
    }

    public function send(Recipient $recipient, MessageContent $content): bool
    {
        return true;
    }
}
