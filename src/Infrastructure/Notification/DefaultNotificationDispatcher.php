<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Domain\Notification\Entity\Notification;
use App\Domain\Notification\Provider\EmailProvider;
use App\Domain\Notification\Provider\MessengerProvider;
use App\Domain\Notification\Provider\PushProvider;
use App\Domain\Notification\Provider\SmsProvider;
use App\Domain\Notification\Service\NotificationDispatcher;
use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\DeliveryAttempt;
use App\Domain\Notification\ValueObject\ProviderName;

final readonly class DefaultNotificationDispatcher implements NotificationDispatcher
{
    public function __construct(
        private iterable $smsProviders,
        private iterable $emailProviders,
        private iterable $pushProviders,
        private iterable $messengerProviders,
        private ProviderSelector $providerSelector,
        private array $enabledChannels = []
    ) {
        $this->enabledChannels = $enabledChannels ?: [
            Channel::Sms->value => true,
            Channel::Email->value => true,
            Channel::Push->value => true,
            Channel::Messenger->value => true,
        ];
    }

    public function dispatch(Notification $notification, array $channels): void
    {
        $recipient = $notification->getRecipient();
        $content = $notification->getMessageContent();
        $allSent = true;
        $anySent = false;

        foreach ($channels as $channel) {
            if (!($this->enabledChannels[$channel->value] ?? true)) {
                continue;
            }

            if ($channel !== $recipient->getChannel()) {
                continue; // Skip channels that don't match recipient type
            }

            $sent = $this->sendViaChannel($notification, $channel, $recipient, $content);
            $anySent = $anySent || $sent;
            $allSent = $allSent && $sent;
        }

        if ($allSent) {
            $notification->markAsSent();
        } elseif ($anySent) {
            $notification->markAsPartiallySent();
        } else {
            $notification->markAsFailed();
        }
    }

    private function sendViaChannel(
        Notification $notification,
        Channel $channel,
        \App\Domain\Notification\ValueObject\Recipient $recipient,
        \App\Domain\Notification\ValueObject\MessageContent $content
    ): bool {
        $failedProviders = [];
        $provider = $this->getProviderToTry($channel, $failedProviders);

        while (null !== $provider) {
            $providerName = $provider->getName();
            try {
                $success = $provider->send($recipient, $content);
                $notification->recordDeliveryAttempt(new DeliveryAttempt($providerName, $success));

                if ($success) {
                    return true;
                }
                $failedProviders[] = $providerName;
            } catch (\Throwable $e) {
                $notification->recordDeliveryAttempt(new DeliveryAttempt(
                    $providerName,
                    false,
                    $e->getMessage()
                ));
                $failedProviders[] = $providerName;
            }

            $provider = $this->getProviderToTry($channel, $failedProviders);
        }

        return false;
    }

    private function getProviderToTry(Channel $channel, array $failedProviders): SmsProvider|EmailProvider|PushProvider|MessengerProvider|null
    {
        $nextName = $this->providerSelector->selectProvider($channel, $failedProviders);
        if (null === $nextName) {
            return null;
        }
        return $this->findProviderByName($nextName);
    }

    private function getProvidersForChannel(Channel $channel): iterable
    {
        return match ($channel) {
            Channel::Sms => $this->smsProviders,
            Channel::Email => $this->emailProviders,
            Channel::Push => $this->pushProviders,
            Channel::Messenger => $this->messengerProviders,
        };
    }

    private function findProviderByName(ProviderName $name): SmsProvider|EmailProvider|PushProvider|MessengerProvider|null
    {
        foreach ([$this->smsProviders, $this->emailProviders, $this->pushProviders, $this->messengerProviders] as $providers) {
            foreach ($providers as $provider) {
                if ($provider->getName() === $name) {
                    return $provider;
                }
            }
        }
        return null;
    }
}
