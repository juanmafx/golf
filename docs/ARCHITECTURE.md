# Architecture

## Overview

Golf is a server-rendered Laravel application. The backend owns routing, authentication, persistence and HTML. The frontend is a small TypeScript and CSS bundle that Vite compiles into `backend/public/build`, which Blade loads through the Vite manifest.

```text
Browser
  │  HTML forms, CSRF token, session cookie
  ▼
Laravel web middleware
  │  session, CSRF, AuthenticateSession, SecurityHeaders
  ├── Fortify routes ──► Actions/Fortify ──► User model
  └── App routes ──────► Controllers ──────► Blade views
                                 │
                                 ▼
                     SQLite (local) or MySQL 8.4
```

## Module structure

```text
backend/
├── app/
│   ├── Actions/Fortify/         Registration, password reset and password rules
│   ├── Http/
│   │   ├── Controllers/         Dashboard and Account/SecuritySettings
│   │   ├── Middleware/          SecurityHeaders
│   │   └── Responses/           Uniform password-reset response
│   ├── Models/User.php          Account with two-factor support
│   └── Providers/               Fortify views and rate limiters
├── config/                      Framework, Fortify, hashing and session settings
├── database/                    Migrations, factory and seeder
├── resources/views/             Layout, auth, account and dashboard templates
├── routes/web.php               Application routes
└── tests/
    ├── Feature/                 Pages, authentication, two-factor, CSRF and headers
    └── Unit/                    Actions and middleware

frontend/
├── src/
│   ├── main.ts                  Bundle entry point
│   ├── modules/navigation.ts    Accessible navigation toggle
│   └── styles/app.css           Application styles
└── tests/                       Vitest suite running in jsdom
```

## Key flows

**Sign-in with two-factor.** `POST /login` validates the password. For an account with confirmed 2FA, the user is not authenticated yet and is redirected to `/two-factor-challenge`. A valid, unused TOTP code or recovery code completes the sign-in.

**Enrollment.** `/account/security` requires a recent password confirmation. Enabling 2FA stores an encrypted secret and shows a QR code. It takes effect only after a valid code is confirmed.

**Password change.** `AuthenticateSession` compares the stored password hash with the session, so existing sessions end when the password changes.

## Data model

`users` holds identity, the Argon2id password hash and encrypted two-factor columns. `password_reset_tokens` and `sessions` support authentication. `cache`, `cache_locks`, `jobs`, `job_batches` and `failed_jobs` are Laravel infrastructure tables. See [`db/schema.sql`](../db/schema.sql).

## Recommended deployment

- Terminate TLS at a reverse proxy and configure Laravel trusted proxies for it.
- Run PHP-FPM with OPcache and serve only `backend/public`.
- Use MySQL 8.4 with a least-privilege application account.
- Build frontend assets during the release and do not commit them.
- Set `APP_ENV=production`, `APP_DEBUG=false` and `SESSION_SECURE_COOKIE=true`.
- Provide `APP_KEY` from a secret manager and back it up, since it decrypts 2FA secrets.
