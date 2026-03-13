<?php

declare(strict_types=1);

namespace App\Domain\Notification\ValueObject;

enum ProviderName: string
{
    case Mailer = 'mailer';
    case Twilio = 'twilio';
    case Vonage = 'vonage';
    case AwsSes = 'aws_ses';
    case Mailgun = 'mailgun';
    case Pushy = 'pushy';
    case Fcm = 'fcm';
    case MetaMessenger = 'meta_messenger';
    case CmMessenger = 'cm_messenger';
}
