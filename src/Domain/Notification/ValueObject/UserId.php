<?php

declare(strict_types=1);

namespace App\Domain\Notification\ValueObject;

final readonly class UserId
{
    public function __construct(
        private string $value
    ) {
        if ('' === $value) {
            throw new \InvalidArgumentException('UserId cannot be empty');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }
}
