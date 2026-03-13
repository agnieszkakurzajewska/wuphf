# Installation

## Requirements

- Docker, Docker Compose 2.10+
- (optional) PHP 8.4+, Composer – for running without Docker

## Installation with Docker

```bash
# 1. Clone / go to project directory
cd wuphf

# 2. Install dependencies (in container)
docker compose run --rm php composer install

# 3. Build images
docker compose build

# 4. Start services
docker compose up -d

# 5. Run migrations
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# 6. Messenger worker (starts with docker compose up, or manually):
docker compose up -d messenger-worker
```

## Installation without Docker

```bash
composer install
cp .env .env.local
# Edit .env.local – DATABASE_URL, MAILER_DSN

# PostgreSQL must be running locally
php bin/console doctrine:migrations:migrate --no-interaction

# Separate terminals:
php bin/console messenger:consume async
symfony server:start  # or php -S localhost:8000 -t public/
```

## Provider configuration

### Email (works with Mailpit by default)

- Docker: Mailpit at `http://localhost:8025`, SMTP `localhost:1025`
- Production: set `MAILER_DSN` in `.env.local` (e.g. SES, Mailgun, SMTP)

### SMS (Twilio)

In `.env.local`:

```
TWILIO_ACCOUNT_SID=AC...
TWILIO_AUTH_TOKEN=...
TWILIO_FROM_NUMBER=+1234567890
```

## Verification

```bash
curl -k -X POST https://localhost:4443/api/notifications/send \
  -H "Content-Type: application/json" \
  -d '{"userId":"u1","recipient":"test@example.com","type":"email","channels":["email"],"subject":"Test","body":"Hello"}'
```

Email in Mailpit: http://localhost:8025
