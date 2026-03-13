<?php

declare(strict_types=1);

namespace App\Domain\Notification\ValueObject;

enum NotificationStatus: string
{
    case Requested = 'requested';
    case Processing = 'processing';
    case PartiallySent = 'partially_sent';
    case Sent = 'sent';
    case Failed = 'failed';
    case RetryScheduled = 'retry_scheduled';
    case Throttled = 'throttled';
}
