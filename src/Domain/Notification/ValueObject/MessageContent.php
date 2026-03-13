<?php

declare(strict_types=1);

namespace App\Domain\Notification\ValueObject;

final readonly class MessageContent
{
    public function __construct(
        private string $subject,
        private string $body
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
