<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Domain\Notification\Entity\Notification;
use App\Domain\Notification\Provider\SmsProvider;
use App\Domain\Notification\Service\NotificationDispatcher;
use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\NotificationId;
use App\Domain\Notification\ValueObject\NotificationType;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\Recipient;
use App\Domain\Notification\ValueObject\UserId;
use App\Domain\Notification\Service\ProviderSelector;
use App\Infrastructure\Notification\ConfigurableFailoverPolicy;
use App\Infrastructure\Notification\DefaultNotificationDispatcher;
use App\Infrastructure\Notification\DefaultProviderSelector;
use App\Infrastructure\Provider\TwilioSmsProvider;
use App\Infrastructure\Provider\VonageSmsProvider;
use PHPUnit\Framework\TestCase;

final class ProviderFailoverTest extends TestCase
{
    public function test_it_switches_to_backup_provider_when_primary_fails(): void
    {
        $twilioFails = new class extends TwilioSmsProvider {
            public function __construct()
            {
                parent::__construct('stub', 'stub', '+1');
            }
            public function send(\App\Domain\Notification\ValueObject\Recipient $recipient, \App\Domain\Notification\ValueObject\MessageContent $content): bool
            {
                throw new \RuntimeException('Twilio API unavailable');
            }
        };
        $vonageWorks = new class extends VonageSmsProvider {
            public function __construct()
            {
                parent::__construct('stub', 'stub', '+1');
            }
            public function send(\App\Domain\Notification\ValueObject\Recipient $recipient, \App\Domain\Notification\ValueObject\MessageContent $content): bool
            {
                return true;
            }
        };

        $failoverPolicy = new ConfigurableFailoverPolicy();
        $providerSelector = new DefaultProviderSelector($failoverPolicy);
        $dispatcher = new DefaultNotificationDispatcher(
            [$twilioFails, $vonageWorks],
            [],
            [],
            [],
            $providerSelector
        );

        $notification = new Notification(
            NotificationId::generate(),
            new UserId('user-1'),
            Recipient::phone('+48123456789'),
            new MessageContent('Subject', 'Body'),
            NotificationType::Sms,
            [Channel::Sms]
        );

        $dispatcher->dispatch($notification, [Channel::Sms]);

        $attempts = $notification->getDeliveryAttempts();
        $this->assertCount(2, $attempts);
        $this->assertFalse($attempts[0]->isSuccess());
        $this->assertSame(ProviderName::Twilio, $attempts[0]->getProvider());
        $this->assertTrue($attempts[1]->isSuccess());
        $this->assertSame(ProviderName::Vonage, $attempts[1]->getProvider());
    }
}
