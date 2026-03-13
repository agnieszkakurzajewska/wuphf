<?php

declare(strict_types=1);

namespace App\Application\Notification\Handler;

use App\Domain\Notification\Repository\NotificationRepository;
use App\Domain\Notification\Service\NotificationDispatcher;
use App\Domain\Notification\ValueObject\NotificationId;
use App\Infrastructure\Queue\Message\RetryNotificationMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RetryNotificationMessageHandler
{
    public function __construct(
        private NotificationRepository $repository,
        private NotificationDispatcher $dispatcher
    ) {
    }

    public function __invoke(RetryNotificationMessage $message): void
    {
        $notification = $this->repository->findById(new NotificationId($message->notificationId));
        if (null === $notification) {
            return;
        }

        $notification->markAsProcessing();
        $this->repository->save($notification);

        $this->dispatcher->dispatch($notification, $notification->getChannels());

        $this->repository->save($notification);
    }
}
