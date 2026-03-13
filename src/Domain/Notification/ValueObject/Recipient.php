<?php

declare(strict_types=1);

namespace App\Domain\Notification\ValueObject;

final readonly class Recipient
{
    public function __construct(
        private string $value,
        private Channel $channel
    ) {
        if ('' === $value) {
            throw new \InvalidArgumentException('Recipient cannot be empty');
        }
    }

    public static function email(string $email): self
    {
        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(\sprintf('Invalid email address: %s', $email));
        }

        return new self($email, Channel::Email);
    }

    public static function phone(string $phone): self
    {
        return new self($phone, Channel::Sms);
    }

    public static function push(string $deviceToken): self
    {
        return new self($deviceToken, Channel::Push);
    }

    public static function messenger(string $psid): self
    {
        return new self($psid, Channel::Messenger);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }
}
