<?php

declare(strict_types=1);

namespace App\Application\Notification\Handler;

use App\Application\Notification\Command\RetryNotificationCommand;
use App\Domain\Notification\Repository\NotificationRepository;
use App\Infrastructure\Queue\Message\RetryNotificationMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class RetryNotificationCommandHandler
{
    public function __construct(
        private NotificationRepository $repository,
        private MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(RetryNotificationCommand $command): void
    {
        $notification = $this->repository->findById($command->notificationId);
        if (null === $notification) {
            return;
        }

        $notification->markAsProcessing();
        $this->repository->save($notification);

        $this->messageBus->dispatch(new RetryNotificationMessage($notification->getId()->toString()));
    }
}
