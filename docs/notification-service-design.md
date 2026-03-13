# Notification Service – Recruitment Task

## Original Task

Create a service that accepts necessary information and sends notifications to customers.

The new service must support:

- **Send notifications via different channels** – Provide an abstraction between at least two different messaging service providers per channel. Use different messaging services (SMS, email, push notification, Facebook Messenger, etc.).

- **Examples of messaging providers** – Emails: AWS SES, Mailgun. SMS: Twilio, Vonage. Push: Pushy, Firebase Cloud Messaging. All listed services offer free trial accounts.

- **Failover support** – If one provider goes down, the service should quickly failover to a different provider without affecting customers. Define several providers per channel (e.g. two for SMS). Delay and resend notifications if all providers fail.

- **Configuration-driven** – Enable/disable different communication channels via configuration. Send the same notification via several different channels.

- **(Bonus) Throttling** – Limit the number of notifications sent to users within an hour (e.g. up to 300 per hour).

- **(Bonus) Usage tracking** – Track which messages were sent, when, and to whom, using a user identifier parameter.

---

## Architecture Overview

This project implements a **Notification Service** responsible for sending notifications through multiple communication channels.

The service abstracts communication providers and ensures:

- multi-channel delivery (SMS, Email, Push, Messenger)
- provider failover (automatic switch to backup when primary fails)
- retry and delayed delivery (configurable retry policy)
- configuration-based channel management
- throttling (rate limit per user, default 300/hour)
- notification tracking (delivery attempts, status, timestamps)

The system is built using:

- **Symfony** – framework
- **Domain Driven Design** – bounded context, aggregates, value objects, ports and adapters
- **Test Driven Development** – unit, application, integration tests
- **Symfony Messenger** – async queue with Doctrine transport
- **Docker** – containerization (php, postgres, mailpit, messenger-worker)

---

## Bounded Context

The system defines a single bounded context: **Notification**.

The service is responsible for: sending notifications, routing messages through providers, managing delivery attempts, retry scheduling, throttling, delivery tracking.

It is not responsible for: user management, authentication, payment systems, or business logic of other services.

---

## Ubiquitous Language

| Term | Meaning |
|------|---------|
| Notification | Message sent to a user |
| Channel | Communication type (SMS, Email, Push, Messenger) |
| Provider | External messaging service implementation |
| Delivery Attempt | Single sending attempt through one provider |
| Retry | Delayed retry of failed notification |
| Throttle | Sending rate limit per user |
| Recipient | Target address (email, phone, token, etc.) |
| UserId | User identifier for tracking and throttling |
| Notification Status | Lifecycle state of the notification |

---

## Supported Channels

| Channel | Primary Provider | Failover Provider |
|---------|------------------|-------------------|
| SMS | Twilio | Vonage |
| Email | Symfony Mailer (Mailpit/SES/Mailgun) | AWS SES, Mailgun |
| Push | Pushy | Firebase Cloud Messaging |
| Facebook Messenger | Meta Messenger Platform | CM.com |

*Implemented:* Email (Symfony Mailer + Mailpit), SMS (Twilio). Others have stub implementations.

---

## Project Structure

```
src/
  Domain/Notification/
    Entity/          – Notification (aggregate root)
    ValueObject/     – NotificationId, UserId, Recipient, Channel, etc.
    Service/        – NotificationDispatcher, FailoverPolicy, ThrottlePolicy, RetryPolicy, ProviderSelector
    Provider/        – SmsProvider, EmailProvider, PushProvider, MessengerProvider (interfaces)
    Repository/      – NotificationRepository (interface)
  Application/Notification/
    Command/         – SendNotificationCommand, RetryNotificationCommand
    Handler/         – Command handlers, message handlers
  Infrastructure/
    Provider/        – TwilioSmsProvider, SymfonyMailerEmailProvider, etc.
    Persistence/     – DoctrineNotificationRepository
    Queue/           – SendNotificationMessage, RetryNotificationMessage
    Notification/    – ConfigurableFailoverPolicy, ConfigurableThrottlePolicy, DefaultNotificationDispatcher
  Controller/        – NotificationController (REST API)
tests/
  Domain/            – NotificationTest, FailoverPolicyTest, ThrottlePolicyTest
  Application/       – SendNotificationCommandHandlerTest
  Integration/       – ProviderFailoverTest, MessengerQueueTest
```

---

## Core Domain Model

**Aggregate root:** `Notification`

```
Notification
 ├── NotificationId
 ├── UserId
 ├── Recipient (value + channel)
 ├── MessageContent (subject, body)
 ├── NotificationType
 ├── NotificationStatus
 ├── Channel[] (channels to use)
 └── DeliveryAttempt[]
```

### Value Objects

- **NotificationId** – UUID, immutable
- **UserId** – user identifier for throttling and tracking
- **Recipient** – target address with channel (email, phone, push token, messenger PSID)
- **MessageContent** – subject and body
- **Channel** – enum: Sms, Email, Push, Messenger
- **ProviderName** – enum: Mailer, Twilio, Vonage, AwsSes, Mailgun, etc.
- **NotificationStatus** – enum: Requested, Processing, PartiallySent, Sent, Failed, RetryScheduled, Throttled
- **NotificationType** – enum: Sms, Email, Push, Messenger
- **DeliveryAttempt** – provider, success flag, error message, timestamp

### Domain Services

- **NotificationDispatcher** – Coordinates sending through configured channels and providers, implements failover
- **FailoverPolicy** – Determines ordered list of providers per channel and next provider after failure
- **ThrottlePolicy** – Checks if user is within rate limit
- **RetryPolicy** – Determines if and when to retry failed notifications
- **ProviderSelector** – Selects provider considering failed ones (failover logic)

---

## Notification Lifecycle

**States:** REQUESTED → PROCESSING → (SENT | PARTIALLY_SENT | FAILED | RETRY_SCHEDULED | THROTTLED)

- **REQUESTED** – Initial state after creation
- **PROCESSING** – Worker is sending
- **SENT** – All channels delivered successfully
- **PARTIALLY_SENT** – At least one channel delivered
- **FAILED** – All providers failed
- **RETRY_SCHEDULED** – Scheduled for retry
- **THROTTLED** – Rejected due to rate limit

### Domain Events (conceptual)

NotificationRequested, NotificationSent, NotificationFailed, NotificationRetried, ProviderFailed, ThrottleLimitReached

---

## Ports and Adapters

**Ports (interfaces):**

- SmsProvider, EmailProvider, PushProvider, MessengerProvider
- NotificationRepository
- Psr\Clock\ClockInterface

**Adapters:**

- SymfonyMailerEmailProvider, TwilioSmsProvider, VonageSmsProvider
- DoctrineNotificationRepository
- Symfony\Component\Clock\NativeClock

---

## Queue and Async Processing

Sending is asynchronous. Flow:

1. `POST /api/notifications/send` → Controller dispatches `SendNotificationCommand`
2. `SendNotificationCommandHandler` creates notification, persists it, dispatches `SendNotificationMessage` to async transport
3. Messenger worker consumes `SendNotificationMessage`
4. `SendNotificationMessageHandler` loads notification, calls `NotificationDispatcher`, updates status

**Transports:** sync (commands), async (SendNotificationMessage, RetryNotificationMessage) via Doctrine transport.

---

## Configuration

- **Throttle:** `notification.throttle_limit_per_hour` (default 300)
- **Retry:** `notification.retry_max_attempts`, `notification.retry_base_delay_seconds`
- **Channels:** configurable via FailoverPolicy channelProviders
- **Providers:** wired via service container, env vars for credentials

---

## Containerization

**Docker services:**

- **php** – Web server (FrankenPHP + Caddy)
- **database** – PostgreSQL
- **mailpit** – SMTP capture for email testing (port 1025, UI 8025)
- **messenger-worker** – Consumes async messages

**Commands:**

- `docker compose up -d` – Start stack
- `php bin/console messenger:consume async` – Run worker manually

---

## Goal of This Design

The design demonstrates:

- **DDD modeling** – Bounded context, aggregate, value objects, domain services
- **Clean architecture** – Separation of Domain, Application, Infrastructure
- **Asynchronous processing** – Messenger with Doctrine transport
- **Failover strategy** – Multiple providers per channel, automatic switch on failure
- **Test-driven development** – Unit tests for domain, application tests for use cases, integration tests for providers and queue
- **Infrastructure abstraction** – Provider interfaces (ports), concrete implementations (adapters)
- **Extensibility** – New providers added via tagging, configuration-driven channel enable/disable
- **Production readiness** – Docker, migrations, env-based configuration, throttling, tracking
