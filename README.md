# Wuphf – Notification Service

Service sending notifications (SMS, Email) via multiple channels with failover, throttling, and async queuing.

**Features:** multi-channel delivery, provider failover, configuration-driven channels, throttling (300/hour per user), usage tracking, Symfony Messenger queue, DDD architecture.

---

## Quick Start

See [docs/INSTALL.md](docs/INSTALL.md) for full instructions.

```bash
docker compose run --rm php composer install
docker compose build && docker compose up -d
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

---

## API

```
POST /api/notifications/send
Content-Type: application/json

{
  "userId": "user-123",
  "recipient": "test@example.com",
  "type": "email",
  "channels": ["email"],
  "subject": "Subject",
  "body": "Body"
}
```

| Field      | Description                    | Example              |
|------------|--------------------------------|----------------------|
| userId     | User identifier (tracking)     | "user-123"           |
| recipient  | Target address                 | "test@example.com"   |
| type       | Channel type                   | email \| sms         |
| channels   | Channels to use                | ["email"] \| ["sms"] |
| subject    | Email subject (or SMS prefix)  | "Hello"              |
| body       | Message content                | "Your message"       |

- **Email:** Mailpit UI at http://localhost:8025 (dev)
- **SMS:** Twilio – set TWILIO_* in .env.local

---

## Documentation

- [docs/INSTALL.md](docs/INSTALL.md) – installation, providers, tests, troubleshooting
- [docs/notification-service-design.md](docs/notification-service-design.md) – architecture, domain model, design decisions
