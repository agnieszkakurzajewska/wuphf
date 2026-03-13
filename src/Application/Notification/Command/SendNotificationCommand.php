<?php

declare(strict_types=1);

namespace App\Application\Notification\Command;

use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\NotificationType;
use App\Domain\Notification\ValueObject\Recipient;
use App\Domain\Notification\ValueObject\UserId;

final readonly class SendNotificationCommand
{
    public function __construct(
        public UserId $userId,
        public Recipient $recipient,
        public MessageContent $messageContent,
        public NotificationType $type,
        public array $channels
    ) {
    }
}
