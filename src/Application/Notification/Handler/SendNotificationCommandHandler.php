<?php

declare(strict_types=1);

namespace App\Application\Notification\Handler;

use App\Application\Notification\Command\SendNotificationCommand;
use App\Domain\Notification\Entity\Notification;
use App\Domain\Notification\Repository\NotificationRepository;
use App\Domain\Notification\Service\ThrottlePolicy;
use App\Domain\Notification\ValueObject\NotificationId;
use App\Infrastructure\Queue\Message\SendNotificationMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class SendNotificationCommandHandler
{
    public function __construct(
        private NotificationRepository $repository,
        private ThrottlePolicy $throttlePolicy,
        private MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(SendNotificationCommand $command): void
    {
        if (!$this->throttlePolicy->isAllowed($command->userId)) {
            $notification = new Notification(
                NotificationId::generate(),
                $command->userId,
                $command->recipient,
                $command->messageContent,
                $command->type,
                $command->channels
            );
            $notification->markThrottled();
            $this->repository->save($notification);

            return;
        }

        $notification = new Notification(
            NotificationId::generate(),
            $command->userId,
            $command->recipient,
            $command->messageContent,
            $command->type,
            $command->channels
        );
        $this->repository->save($notification);

        $this->messageBus->dispatch(new SendNotificationMessage($notification->getId()->toString()));
    }
}
