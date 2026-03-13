<?php

declare(strict_types=1);

namespace App\Tests\Domain\Notification;

use App\Domain\Notification\Repository\NotificationRepository;
use App\Domain\Notification\ValueObject\UserId;
use App\Infrastructure\Notification\ConfigurableThrottlePolicy;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class ThrottlePolicyTest extends TestCase
{
    public function test_it_allows_notifications_below_limit(): void
    {
        $repository = $this->createMock(NotificationRepository::class);
        $repository->method('countByUserIdSince')->willReturn(10);

        $clock = $this->createStub(ClockInterface::class);
        $clock->method('now')->willReturn(new \DateTimeImmutable('2025-03-09 12:00:00'));
        $policy = new ConfigurableThrottlePolicy($repository, $clock, 300);

        $this->assertTrue($policy->isAllowed(new UserId('user-1')));
    }

    public function test_it_blocks_notifications_when_limit_exceeded(): void
    {
        $repository = $this->createMock(NotificationRepository::class);
        $repository->method('countByUserIdSince')->willReturn(300);

        $clock = $this->createStub(ClockInterface::class);
        $clock->method('now')->willReturn(new \DateTimeImmutable('2025-03-09 12:00:00'));
        $policy = new ConfigurableThrottlePolicy($repository, $clock, 300);

        $this->assertFalse($policy->isAllowed(new UserId('user-1')));
    }
}
