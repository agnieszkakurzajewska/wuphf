<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Domain\Notification\Repository\NotificationRepository;
use App\Domain\Notification\Service\ThrottlePolicy;
use App\Domain\Notification\ValueObject\UserId;
use Psr\Clock\ClockInterface;

final readonly class ConfigurableThrottlePolicy implements ThrottlePolicy
{
    public function __construct(
        private NotificationRepository $repository,
        private ClockInterface $clock,
        private int $limitPerHour = 300
    ) {
    }

    public function isAllowed(UserId $userId): bool
    {
        $since = $this->clock->now()->modify('-1 hour');
        $count = $this->repository->countByUserIdSince($userId, $since);

        return $count < $this->limitPerHour;
    }

    public function getLimitPerHour(): int
    {
        return $this->limitPerHour;
    }
}
