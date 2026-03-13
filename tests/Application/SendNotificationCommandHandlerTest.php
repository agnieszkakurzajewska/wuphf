<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Application\Notification\Command\SendNotificationCommand;
use App\Application\Notification\Handler\SendNotificationCommandHandler;
use App\Domain\Notification\Entity\Notification;
use App\Domain\Notification\Repository\NotificationRepository;
use App\Domain\Notification\Service\ThrottlePolicy;
use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\NotificationType;
use App\Domain\Notification\ValueObject\Recipient;
use App\Domain\Notification\ValueObject\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class SendNotificationCommandHandlerTest extends TestCase
{
    public function test_it_dispatches_notification_to_message_bus(): void
    {
        $repository = $this->createMock(NotificationRepository::class);
        $repository->expects($this->once())->method('save')->with($this->isInstanceOf(Notification::class));

        $throttlePolicy = $this->createStub(ThrottlePolicy::class);
        $throttlePolicy->method('isAllowed')->willReturn(true);

        $dispatchedMessages = [];
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->method('dispatch')->willReturnCallback(function ($message) use (&$dispatchedMessages) {
            $dispatchedMessages[] = $message;
            return new Envelope($message);
        });

        $handler = new SendNotificationCommandHandler($repository, $throttlePolicy, $messageBus);

        $command = new SendNotificationCommand(
            new UserId('user-1'),
            Recipient::email('test@example.com'),
            new MessageContent('Subject', 'Body'),
            NotificationType::Email,
            [Channel::Email]
        );

        ($handler)($command);

        $this->assertCount(1, $dispatchedMessages);
        $this->assertSame('App\Infrastructure\Queue\Message\SendNotificationMessage', $dispatchedMessages[0]::class);
    }

    public function test_it_records_throttled_notification_when_limit_exceeded(): void
    {
        $repository = $this->createMock(NotificationRepository::class);
        $repository->expects($this->once())->method('save');

        $throttlePolicy = $this->createStub(ThrottlePolicy::class);
        $throttlePolicy->method('isAllowed')->willReturn(false);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->never())->method('dispatch');

        $handler = new SendNotificationCommandHandler($repository, $throttlePolicy, $messageBus);

        $command = new SendNotificationCommand(
            new UserId('user-1'),
            Recipient::email('test@example.com'),
            new MessageContent('Subject', 'Body'),
            NotificationType::Email,
            [Channel::Email]
        );

        ($handler)($command);
    }
}
