<?php

declare(strict_types=1);

namespace App\Domain\Notification\Service;

use App\Domain\Notification\Entity\Notification;
use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\DeliveryAttempt;

interface NotificationDispatcher
{
    public function dispatch(Notification $notification, array $channels): void;
}
