<?php

declare(strict_types=1);

namespace App\Domain\Notification\ValueObject;

enum NotificationType: string
{
    case Sms = 'sms';
    case Email = 'email';
    case Push = 'push';
    case Messenger = 'messenger';
}
