<?php

declare(strict_types=1);

namespace App\Domain\Notification\ValueObject;

final readonly class NotificationId
{
    public function __construct(
        private string $value
    ) {
        if ('' === $value) {
            throw new \InvalidArgumentException('NotificationId cannot be empty');
        }
    }

}
