# Installation

## Requirements

- Docker, Docker Compose 2.10+
- (optional) PHP 8.4+, Composer – for running without Docker

---

## Installation with Docker

```bash
# 1. Clone or go to project directory
cd wuphf

# 2. Install dependencies (run inside container)
docker compose run --rm php composer install

# 3. Build images
docker compose build

# 4. Start all services (php, database, mailpit, messenger-worker)
docker compose up -d

# 5. Run database migrations
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# 6. Messenger worker – starts automatically with docker compose up,
#    or run manually:
docker compose up -d messenger-worker
```

---

## Installation without Docker

```bash
composer install
cp .env .env.local
# Edit .env.local – DATABASE_URL, MAILER_DSN

# PostgreSQL must be running locally
php bin/console doctrine:migrations:migrate --no-interaction

# Run in separate terminals:
php bin/console messenger:consume async
symfony server:start
# or: php -S localhost:8000 -t public/
```

---

## Provider Configuration

### Email (works with Mailpit by default)

- **Docker:** Mailpit receives all emails. Web UI: `http://localhost:8025`, SMTP: `localhost:1025`
- **Production:** Set `MAILER_DSN` in `.env.local`, e.g.:
  - `MAILER_DSN=smtp://user:pass@smtp.example.com:587`
  - `MAILER_DSN=ses://ACCESS_KEY:SECRET_KEY@default?region=eu-west-1`
  - `MAILER_DSN=mailgun+api://KEY@default?domain=mg.example.com`
- `MAILER_FROM_EMAIL` – sender address (default: noreply@localhost)

### SMS (Twilio)

Add to `.env.local`:

```
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

Without these, SMS requests will fail (Twilio returns false when credentials are empty).

---

## Verification

Send a test email:

```bash
curl -k -X POST https://localhost:4443/api/notifications/send \
  -H "Content-Type: application/json" \
  -d '{"userId":"u1","recipient":"test@example.com","type":"email","channels":["email"],"subject":"Test","body":"Hello"}'
```

Expected: HTTP 202 Accepted. Check Mailpit at `http://localhost:8025` for the email.

---

## Running Tests

```bash
# Start database first
docker compose up -d database

# Run all tests
docker compose run --rm php php bin/phpunit

# Run specific suite
docker compose run --rm php php bin/phpunit tests/Domain/
docker compose run --rm php php bin/phpunit tests/Application/
```

---

## Troubleshooting

- **Database connection failed:** Ensure `database` service is running. Docker Compose waits for it on first run.
- **HTTPS certificate warning:** Use `-k` with curl to skip verification, or accept the self-signed cert in browser.
- **No email in Mailpit:** Check `messenger-worker` is running. Emails are sent asynchronously.
- **SMS not sending:** Add valid Twilio credentials to `.env.local`.
