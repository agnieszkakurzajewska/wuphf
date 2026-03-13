<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
final readonly class SendNotificationMessage
{
    public function __construct(
        public string $notificationId
    ) {
    }
}
