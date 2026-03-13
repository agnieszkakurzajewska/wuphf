<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Notification\Entity\Notification;
use App\Domain\Notification\Repository\NotificationRepository;
use App\Domain\Notification\ValueObject\NotificationId;
use App\Domain\Notification\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineNotificationRepository implements NotificationRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function save(Notification $notification): void
    {
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function findById(NotificationId $id): ?Notification
    {
        return $this->entityManager->getRepository(Notification::class)->find($id->toString());
    }

    public function findByUserId(UserId $userId, \DateTimeImmutable $since): array
    {
        return $this->entityManager->getRepository(Notification::class)->createQueryBuilder('n')
            ->where('n.userId = :userId')
            ->andWhere('n.createdAt >= :since')
            ->setParameter('userId', $userId->toString())
            ->setParameter('since', $since)
            ->getQuery()
            ->getResult();
    }

    public function countByUserIdSince(UserId $userId, \DateTimeImmutable $since): int
    {
        return (int) $this->entityManager->getRepository(Notification::class)->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.userId = :userId')
            ->andWhere('n.createdAt >= :since')
            ->setParameter('userId', $userId->toString())
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
