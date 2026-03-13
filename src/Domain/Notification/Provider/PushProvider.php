<?php

declare(strict_types=1);

namespace App\Domain\Notification\Provider;

use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\Recipient;

interface PushProvider
{
    public function getName(): ProviderName;

    public function send(Recipient $recipient, MessageContent $content): bool;
}
