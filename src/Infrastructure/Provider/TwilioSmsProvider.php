<?php

declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Domain\Notification\Provider\SmsProvider;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\Recipient;
use Twilio\Rest\Client;

final readonly class TwilioSmsProvider implements SmsProvider
{
    public function __construct(
        private string $accountSid,
        private string $authToken,
        private string $fromNumber
    ) {
    }

    public function getName(): ProviderName
    {
        return ProviderName::Twilio;
    }

    public function send(Recipient $recipient, MessageContent $content): bool
    {
        if ('' === $this->accountSid || '' === $this->authToken) {
            return false;
        }
        $client = new Client($this->accountSid, $this->authToken);
        $client->messages->create($recipient->getValue(), [
            'from' => $this->fromNumber,
            'body' => $content->getBody(),
        ]);
        return true;
    }
}
