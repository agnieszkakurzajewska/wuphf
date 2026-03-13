# Wuphf – Notification Service

Service sending notifications (SMS, Email) with failover, throttling and queuing.

## Quick Start

Details: [docs/INSTALL.md](docs/INSTALL.md)

```bash
docker compose run --rm php composer install
docker compose build && docker compose up -d
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

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

- `type`: email | sms
- `channels`: ["email"] | ["sms"]
- Email – Mailpit UI: http://localhost:8025
- SMS – Twilio (TWILIO_* in .env.local)

## Documentation

- [docs/INSTALL.md](docs/INSTALL.md) – installation
- [docs/notification-service-design.md](docs/notification-service-design.md) – architecture
