<?php

declare(strict_types=1);

namespace App\Domain\Notification\Entity;

use App\Domain\Notification\ValueObject\Channel;
use App\Domain\Notification\ValueObject\DeliveryAttempt;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\NotificationId;
use App\Domain\Notification\ValueObject\NotificationStatus;
use App\Domain\Notification\ValueObject\NotificationType;
use App\Domain\Notification\ValueObject\Recipient;
use App\Domain\Notification\ValueObject\UserId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'notification')]
class Notification
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $userId;

    #[ORM\Column(type: 'string', length: 512)]
    private string $recipientValue;

    #[ORM\Column(type: 'string', length: 100, enumType: Channel::class)]
    private Channel $recipientChannel;

    #[ORM\Column(type: 'string', length: 500)]
    private string $subject;

    #[ORM\Column(type: 'text')]
    private string $body;

    #[ORM\Column(type: 'string', length: 50, enumType: NotificationStatus::class)]
    private NotificationStatus $status;

    #[ORM\Column(type: 'string', length: 50, enumType: NotificationType::class)]
    private NotificationType $type;

    #[ORM\Column(type: 'json')]
    private array $channels;

    #[ORM\Column(type: 'json')]
    private array $deliveryAttempts;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    public function __construct(
        NotificationId $id,
        UserId $userId,
        Recipient $recipient,
        MessageContent $content,
        NotificationType $type,
        array $channels
    ) {
        $this->id = $id->toString();
        $this->userId = $userId->toString();
        $this->recipientValue = $recipient->getValue();
        $this->recipientChannel = $recipient->getChannel();
        $this->subject = $content->getSubject();
        $this->body = $content->getBody();
        $this->type = $type;
        $this->channels = array_map(static fn (Channel $c) => $c->value, $channels);
        $this->status = NotificationStatus::Requested;
        $this->deliveryAttempts = [];
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): NotificationId
    {
        return new NotificationId($this->id);
    }

    public function getUserId(): UserId
    {
        return new UserId($this->userId);
    }

    public function getRecipient(): Recipient
    {
        return match ($this->recipientChannel) {
            Channel::Email => Recipient::email($this->recipientValue),
            Channel::Sms => Recipient::phone($this->recipientValue),
            Channel::Push => Recipient::push($this->recipientValue),
            Channel::Messenger => Recipient::messenger($this->recipientValue),
        };
    }

    public function getMessageContent(): MessageContent
    {
        return new MessageContent($this->subject, $this->body);
    }

    public function getStatus(): NotificationStatus
    {
        return $this->status;
    }

    public function getChannels(): array
    {
        return array_map(static fn (string $c) => Channel::from($c), $this->channels);
    }

    public function getDeliveryAttempts(): array
    {
        return array_map(
            static fn (array $a) => new DeliveryAttempt(
                \App\Domain\Notification\ValueObject\ProviderName::from($a['provider']),
                $a['success'],
                $a['errorMessage'] ?? null,
                isset($a['attemptedAt']) ? new \DateTimeImmutable($a['attemptedAt']) : null
            ),
            $this->deliveryAttempts
        );
    }

    public function recordDeliveryAttempt(DeliveryAttempt $attempt): void
    {
        $this->deliveryAttempts[] = [
            'provider' => $attempt->getProvider()->value,
            'success' => $attempt->isSuccess(),
            'errorMessage' => $attempt->getErrorMessage(),
            'attemptedAt' => $attempt->getAttemptedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    public function markAsProcessing(): void
    {
        $this->status = NotificationStatus::Processing;
    }

    public function markAsSent(): void
    {
        $this->status = NotificationStatus::Sent;
        $this->sentAt = new \DateTimeImmutable();
    }

    public function markAsFailed(): void
    {
        $this->status = NotificationStatus::Failed;
    }

    public function markAsPartiallySent(): void
    {
        $this->status = NotificationStatus::PartiallySent;
    }

    public function markRetryScheduled(): void
    {
        $this->status = NotificationStatus::RetryScheduled;
    }

    public function markThrottled(): void
    {
        $this->status = NotificationStatus::Throttled;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }
}
