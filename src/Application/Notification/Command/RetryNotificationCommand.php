<?php

declare(strict_types=1);

namespace App\Application\Notification\Command;

use App\Domain\Notification\ValueObject\NotificationId;

final readonly class RetryNotificationCommand
{
    public function __construct(
        public NotificationId $notificationId
    ) {
    }
}
