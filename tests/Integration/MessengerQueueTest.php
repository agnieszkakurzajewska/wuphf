<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Infrastructure\Queue\Message\SendNotificationMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerQueueTest extends KernelTestCase
{
    public function test_it_creates_valid_send_notification_message(): void
    {
        $message = new SendNotificationMessage('uuid-123');
        $this->assertSame('uuid-123', $message->notificationId);
    }

    public function test_it_puts_notification_message_on_queue(): void
    {
        self::bootKernel();
        $bus = self::getContainer()->get(MessageBusInterface::class);

        $message = new SendNotificationMessage('test-notification-id');
        $envelope = $bus->dispatch($message);

        $this->assertNotNull($envelope->getMessage());
        $this->assertSame('test-notification-id', $message->notificationId);
    }
}
