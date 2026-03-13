<?php

declare(strict_types=1);

namespace App\Domain\Notification\Service;

use App\Domain\Notification\ValueObject\UserId;

interface ThrottlePolicy
{
    public function isAllowed(UserId $userId): bool;

    public function getLimitPerHour(): int;
}
