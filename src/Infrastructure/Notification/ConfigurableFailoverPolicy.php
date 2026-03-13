<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Domain\Notification\Service\FailoverPolicy;
use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\ProviderName;

final readonly class ConfigurableFailoverPolicy implements FailoverPolicy
{
    public function __construct(
        private array $channelProviders = []
    ) {
        $this->channelProviders = $channelProviders ?: [
            Channel::Sms->value => [ProviderName::Twilio, ProviderName::Vonage],
            Channel::Email->value => [ProviderName::Mailer, ProviderName::AwsSes, ProviderName::Mailgun],
            Channel::Push->value => [ProviderName::Pushy, ProviderName::Fcm],
            Channel::Messenger->value => [ProviderName::MetaMessenger, ProviderName::CmMessenger],
        ];
    }

    public function getProvidersForChannel(Channel $channel): array
    {
        return $this->channelProviders[$channel->value] ?? [];
    }

    public function getNextProvider(Channel $channel, array $failedProviders): ?ProviderName
    {
        $providers = $this->getProvidersForChannel($channel);
        $failedNames = array_map(static fn (ProviderName $p) => $p->value, $failedProviders);

        foreach ($providers as $provider) {
            $name = $provider instanceof ProviderName ? $provider->value : $provider;
            if (!\in_array($name, $failedNames, true)) {
                return $provider instanceof ProviderName ? $provider : ProviderName::from($name);
            }
        }

        return null;
    }
}
