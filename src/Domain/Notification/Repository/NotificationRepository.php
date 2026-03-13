<?php

declare(strict_types=1);

namespace App\Domain\Notification\Repository;

use App\Domain\Notification\Entity\Notification;
use App\Domain\Notification\ValueObject\NotificationId;
use App\Domain\Notification\ValueObject\UserId;

interface NotificationRepository
{
    public function save(Notification $notification): void;

    public function findById(NotificationId $id): ?Notification;

    public function findByUserId(UserId $userId, \DateTimeImmutable $since): array;

    public function countByUserIdSince(UserId $userId, \DateTimeImmutable $since): int;
}
