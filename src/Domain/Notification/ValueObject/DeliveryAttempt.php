<?php

declare(strict_types=1);

namespace App\Domain\Notification\ValueObject;

final readonly class DeliveryAttempt
{
    public function __construct(
        private ProviderName $provider,
        private bool $success,
        private ?string $errorMessage = null,
        private ?\DateTimeImmutable $attemptedAt = null
    ) {
        $this->attemptedAt ??= new \DateTimeImmutable();
    }

    public function getProvider(): ProviderName
    {
        return $this->provider;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getAttemptedAt(): \DateTimeImmutable
    {
        return $this->attemptedAt;
    }
}
