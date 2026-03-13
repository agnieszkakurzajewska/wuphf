<?php

declare(strict_types=1);

namespace App\Tests\Domain\Notification;

use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Infrastructure\Notification\ConfigurableFailoverPolicy;
use PHPUnit\Framework\TestCase;

final class FailoverPolicyTest extends TestCase
{
    private ConfigurableFailoverPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new ConfigurableFailoverPolicy();
    }

    public function test_it_returns_primary_provider_first(): void
    {
        $providers = $this->policy->getProvidersForChannel(Channel::Sms);

        $this->assertNotEmpty($providers);
        $this->assertSame(ProviderName::Twilio, $providers[0]);
    }

    public function test_it_returns_secondary_provider_when_primary_failed(): void
    {
        $failedProviders = [ProviderName::Twilio];
        $next = $this->policy->getNextProvider(Channel::Sms, $failedProviders);

        $this->assertSame(ProviderName::Vonage, $next);
    }

    public function test_it_returns_null_when_all_providers_failed(): void
    {
        $failedProviders = [ProviderName::Twilio, ProviderName::Vonage];
        $next = $this->policy->getNextProvider(Channel::Sms, $failedProviders);

        $this->assertNull($next);
    }
}
