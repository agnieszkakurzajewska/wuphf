<?php

declare(strict_types=1);

namespace App\Tests\Domain\Notification;

use App\Domain\Notification\Entity\Notification;
use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\DeliveryAttempt;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\NotificationId;
use App\Domain\Notification\ValueObject\NotificationStatus;
use App\Domain\Notification\ValueObject\NotificationType;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\Recipient;
use App\Domain\Notification\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

final class NotificationTest extends TestCase
{
    public function test_it_creates_notification(): void
    {
        $id = NotificationId::generate();
        $userId = new UserId('user-123');
        $recipient = Recipient::email('test@example.com');
        $content = new MessageContent('Subject', 'Body');
        $channels = [Channel::Email];

        $notification = new Notification($id, $userId, $recipient, $content, NotificationType::Email, $channels);

        $this->assertTrue($notification->getId()->equals($id));
        $this->assertSame('user-123', $notification->getUserId()->toString());
        $this->assertSame(NotificationStatus::Requested, $notification->getStatus());
    }

    public function test_it_has_requested_status_initially(): void
    {
        $notification = $this->createNotification();

        $this->assertSame(NotificationStatus::Requested, $notification->getStatus());
    }

    public function test_it_records_delivery_attempt(): void
    {
        $notification = $this->createNotification();
        $attempt = new DeliveryAttempt(ProviderName::Twilio, true);

        $notification->recordDeliveryAttempt($attempt);

        $attempts = $notification->getDeliveryAttempts();
        $this->assertCount(1, $attempts);
        $this->assertSame(ProviderName::Twilio, $attempts[0]->getProvider());
        $this->assertTrue($attempts[0]->isSuccess());
    }

    public function test_it_marks_notification_as_sent(): void
    {
        $notification = $this->createNotification();

        $notification->markAsSent();

        $this->assertSame(NotificationStatus::Sent, $notification->getStatus());
        $this->assertNotNull($notification->getSentAt());
    }

    private function createNotification(): Notification
    {
        return new Notification(
            NotificationId::generate(),
            new UserId('user-1'),
            Recipient::email('test@example.com'),
            new MessageContent('Test', 'Body'),
            NotificationType::Email,
            [Channel::Email]
        );
    }
}
