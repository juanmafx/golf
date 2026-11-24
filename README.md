# Golf

A Laravel 13 web application that provides an account system: registration, sign-in, password reset and TOTP two-factor authentication, with a separate TypeScript frontend build.

> **Project history:** originally created in 2018 as a Laravel 5.4 application, modernized in 2026.

## Purpose

Golf is an authentication foundation. Despite the name, it contains **no golf-specific features** such as courses, scores or players. The original project was the standard Laravel 5.4 authentication scaffold with a project name and a customized dashboard message.

## Why this project exists

It began as a starting point for a Laravel application. The 2026 version keeps that scope and brings it to a current baseline: supported runtimes, secure credential storage, two-factor sign-in, automated tests and a reproducible CI pipeline.

## Features

- Account registration with a 12 to 128 character password policy
- Sign-in, sign-out and optional remember-me
- Password reset by email, with the same response for known and unknown addresses and links built from `APP_URL`
- TOTP two-factor authentication with QR enrollment, confirmation and single-use recovery codes
- Argon2id password hashing, which applies a unique random salt to every password
- Encrypted two-factor secrets, recovery codes and sessions
- Rate limiting on authentication endpoints
- CSRF protection and a nonce-based Content Security Policy
- Health endpoint at `/up`

## Architecture

```text
golf/
├── backend/            Laravel 13 application (routes, controllers, Blade views, migrations, tests)
├── frontend/           TypeScript and CSS source, built by Vite into backend/public/build
├── db/schema.sql       MySQL 8.4 schema generated from the migrations
├── docs/               Architecture and security audit
├── reports/tests/      Committed results from a real test run
└── scripts/            Containerized test runner and report generator
```

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for the module structure and request flows.

## Requirements

| Tool | Version |
| --- | --- |
| PHP | 8.4 or 8.5 with `dom`, `mbstring`, `pdo_sqlite` or `pdo_mysql`, `sodium`, `xml` |
| Composer | 2.x |
| Node.js | 24.15 or newer within the 24 line |
| Docker | Optional, used by `scripts/run-tests.sh` |

## Quick start

```bash
cd frontend
npm ci --ignore-scripts
npm run build

cd ../backend
composer install --no-scripts
php artisan package:discover
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

Open <http://localhost:8000>.

## Usage guide

1. Select **Register** and create an account.
2. Open **Security** and confirm your password.
3. Select **Enable two-factor authentication** and scan the QR code with an authenticator app.
4. Enter the current code to activate it, then store the recovery codes offline.
5. On later sign-ins, enter a current code or one unused recovery code.

With the default `MAIL_MAILER=log`, password reset emails are written to `backend/storage/logs/mail.log`. That file contains working reset links, so use it only for local development.

## Configuration

Settings live in `backend/.env`. The most relevant ones are:

| Variable | Default | Notes |
| --- | --- | --- |
| `APP_KEY` | empty | Generate with `php artisan key:generate`. Rotating it makes stored 2FA secrets unreadable unless the old key is kept in `APP_PREVIOUS_KEYS`. |
| `APP_ENV` | `local` | Use `production` when deployed. Outside `local`, requests for any host other than the `APP_URL` host are rejected. |
| `APP_URL` | `http://localhost:8000` | Must be the public URL. Password reset links and the trusted host are derived from it. |
| `APP_DEBUG` | `false` | Keep disabled outside local debugging. |
| `DB_CONNECTION` | `sqlite` | Use `mysql` with the `DB_*` settings for MySQL. |
| `HASH_DRIVER` | `argon2id` | Password hashing algorithm. |
| `SESSION_ENCRYPT` | `true` | Encrypts session payloads. |
| `SESSION_SECURE_COOKIE` | unset | When unset, cookies are HTTPS-only if `APP_ENV=production`. |
| `MAIL_MAILER` | `log` | Configure a real transport for production. |
| `MAIL_LOG_CHANNEL` | `mail` | Log channel used by the `log` mailer. |

## Database

Laravel migrations are the source of truth. [db/schema.sql](db/schema.sql) contains the equivalent `CREATE TABLE` statements for MySQL 8.4:

```bash
mysql -u <user> -p <database> < db/schema.sql
```

Use one approach for a given database. Loading the SQL file does not record migrations in Laravel's `migrations` table.

## Testing

```bash
./scripts/run-tests.sh
```

The script runs both suites in pinned containers with networking disabled and writes [reports/tests/](reports/tests/TEST_REPORT.md). It expects `backend/vendor` and `frontend/node_modules` to be installed. To run the suites directly:

```bash
cd backend && vendor/bin/phpunit
cd frontend && npm test
```

## Security notes

See [SECURITY.md](SECURITY.md) and [docs/SECURITY_AUDIT.md](docs/SECURITY_AUDIT.md). Two-factor authentication is optional per account. Deployments behind a reverse proxy must configure trusted proxies so that rate limiting and HTTPS detection see the real client.

## Versioning and license

The current version is in [VERSION](VERSION), with changes in [CHANGELOG.md](CHANGELOG.md). `backend/composer.json` declares the MIT license, inherited from the Laravel skeleton, and the repository has no standalone `LICENSE` file. Laravel and other third-party components are credited in [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md).
