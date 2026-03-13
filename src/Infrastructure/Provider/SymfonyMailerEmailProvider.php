<?php

declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Domain\Notification\Provider\EmailProvider;
use App\Domain\Notification\ValueObject\MessageContent;
use App\Domain\Notification\ValueObject\ProviderName;
use App\Domain\Notification\ValueObject\Recipient;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SymfonyMailerEmailProvider implements EmailProvider
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromEmail
    ) {
    }

    public function getName(): ProviderName
    {
        return ProviderName::Mailer;
    }

    public function send(Recipient $recipient, MessageContent $content): bool
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($recipient->getValue())
            ->subject($content->getSubject())
            ->text($content->getBody());
        $this->mailer->send($email);
        return true;
    }
}
