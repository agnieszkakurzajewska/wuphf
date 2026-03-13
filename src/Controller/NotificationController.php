<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Notification\Command\SendNotificationCommand;
use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\NotificationType;
use App\Domain\Notification\ValueObject\Recipient;
use App\Domain\Notification\ValueObject\UserId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notifications', name: 'api_notifications_')]
final readonly class NotificationController
{
    public function __construct(
        private MessageBusInterface $messageBus
    ) {
    }

    #[Route('/send', name: 'send', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        $data = \json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        $userId = new UserId($data['userId'] ?? '');
        $channels = [];
        foreach ($data['channels'] ?? [] as $c) {
            $channels[] = Channel::from($c);
        }
        $type = NotificationType::from($data['type'] ?? 'email');

        $recipient = match ($type) {
            NotificationType::Sms => Recipient::phone($data['recipient'] ?? ''),
            NotificationType::Email => Recipient::email($data['recipient'] ?? ''),
            NotificationType::Push => Recipient::push($data['recipient'] ?? ''),
            NotificationType::Messenger => Recipient::messenger($data['recipient'] ?? ''),
        };

        $content = new MessageContent(
            $data['subject'] ?? '',
            $data['body'] ?? ''
        );

        $command = new SendNotificationCommand($userId, $recipient, $content, $type, $channels);
        $this->messageBus->dispatch($command);

        return new JsonResponse(['status' => 'accepted'], Response::HTTP_ACCEPTED);
    }
}
