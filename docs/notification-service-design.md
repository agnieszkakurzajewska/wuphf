# Notification Service – Recruitment Task
#### Task

Create a service that accepts necessary information and sends notifications to customers.

The new service should be capable of the following:

- **Send notifications via different channels:**
- **Examples of some messaging providers:**
- **Failover support:**
- **Configuration-driven:**
- **(Bonus) Throttling:**
- **(Bonus) Usage tracking:**
---

## Architecture Overview

This project implements a **Notification Service** responsible for sending notifications through multiple communication channels.

The service abstracts communication providers and ensures:

- multi-channel delivery
- provider failover
- retry and delayed delivery
- configuration-based channel management
- throttling
- notification tracking

The system is built using:

- Symfony
- Domain Driven Design
- Test Driven Development
- Symfony Messenger (queue)
- Docker containerization

---

## Supported Channels

| Channel | Provider 1 | Provider 2 |
|---------|------------|------------|
| SMS | Twilio | Vonage |
| Email | AWS SES | Mailgun |
| Push | Pushy | Firebase Cloud Messaging |
| Facebook Messenger | Meta Messenger Platform | CM.com |

---

## Core Domain Model

**Aggregate root:** `Notification`

```
Notification
 ├── NotificationId
 ├── UserId
 ├── Recipient
 ├── MessageContent
 ├── NotificationType
 ├── NotificationStatus
 ├── Channel[]
 └── DeliveryAttempt[]
```

### Value Objects

- NotificationId
- UserId
- Recipient
- EmailAddress
- PhoneNumber
- MessageContent
- Channel
- ProviderName
- NotificationStatus
- NotificationType

### Domain Services

- NotificationDispatcher
- FailoverPolicy
- ThrottlePolicy
- RetryPolicy
- ProviderSelector

## Notification Lifecycle

**States:** REQUESTED, PROCESSING, PARTIALLY_SENT, SENT, FAILED, RETRY_SCHEDULED, THROTTLED

### Domain Events

- NotificationRequested
- NotificationSent
- NotificationFailed
- NotificationRetried
- ProviderFailed
- ThrottleLimitReached

---

## Containerization

Docker services: php, postgres, messenger-worker

Running project: `docker compose up`  
Running worker: `php bin/console messenger:consume async`

---

## Goal of This Design
