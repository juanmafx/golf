# Security Audit

Audit performed in November 2026 during the modernization to version 1.0.0.

## Method

- `gitleaks` over the working tree and full git history: no findings
- `composer audit` on the original and updated lock files
- `npm audit` on the new frontend lock file
- Manual review of routes, controllers, views and configuration
- Adversarial regression tests in `backend/tests/Feature/Security/HardeningTest.php`
- One manual run of registration, 2FA enrollment, sign-in, recovery, replay and password reset in headless Chromium against a local server
- Comparison of migrations and `db/schema.sql` on MySQL 8.4: identical columns and indexes

## Findings and resolution

| Area | Original state | Resolution |
| --- | --- | --- |
| End-of-life stack | Laravel 5.4 on PHP 5.6 or newer | Laravel 13 on PHP 8.4 or newer |
| Known vulnerable dependencies | 42 advisories across 12 Composer packages | 0 advisories reported |
| Frontend dependencies | Unpinned, no lock file, jQuery, Vue 2, Laravel Mix 1 | Pinned Vite and TypeScript toolchain with a lock file, 0 advisories reported |
| Committed build artifacts | Compiled `public/js/app.js` and `public/css/app.css` | Removed and gitignored |
| Password hashing | bcrypt, 6 character minimum | Salted Argon2id, 12 to 128 characters |
| Two-factor authentication | None | TOTP with encrypted secrets, replay rejection and recovery codes |
| Brute force | Login throttle only | Limits on login, two-factor and state-changing auth routes |
| Account enumeration | Reset form revealed unknown emails | Uniform reset response |
| Inline script | `onclick` handler on logout | Removed, strict nonce-based CSP |
| Response headers | None | CSP, HSTS over HTTPS, frame, referrer and permissions policies |
| Sessions | Unencrypted, not invalidated on password change | Encrypted, invalidated on password change |
| Debug default | `APP_DEBUG=true` in the example file | `false` |
| Broken API route | `/api/user` used a token column that did not exist | Removed |

No SQL injection, unsafe `eval` or hardcoded production secret was found in the original application code.

## Defects found in the modernized code and fixed

| Defect | Impact | Resolution |
| --- | --- | --- |
| Reset links used the request `Host` header | A forged host could send a victim a link that leaks the reset token | Links are built from `APP_URL`, and other hosts are rejected outside `local` |
| `force` on the 2FA enable endpoint | Replaced a confirmed secret and could lock the user out | `force` applies only to unconfirmed enrollment |
| Non-string login identifier | Unauthenticated requests caused a 500 | Handled as a validation error |
| Recovery codes and secrets were cacheable | Browser storage could retain them | Authenticated responses use `no-store` |
| Reads and writes shared one rate-limit counter | Normal page views exhausted the write limit | Separate counters |
| Headers applied only to web routes | 404 and other unrouted responses had no policy | Applied globally |
| Framework error pages used inline styles | CSP violations on error responses | CSP-compliant error layout |
| Unused signed storage routes | Unneeded upload and download surface | Disabled |
| Example file forced insecure cookies | Copying it to production kept cookies usable over HTTP | Setting removed, production defaults to secure cookies |
| Log mailer output was filtered by `LOG_LEVEL=info` | Local password reset emails were silently lost | Dedicated `mail` log channel |

## Open items

- Registration necessarily reports when an email is already in use.
- Email verification is not enabled.
- Two-factor authentication is optional per account.
- Trusted proxies must be configured per deployment.
- Login limits apply per email and IP and per IP, so guessing distributed across many IPs is not capped per account.
- The framework equalizes reset timing to about 200 ms. Synchronous mail delivery slower than that can reveal whether an account exists, so queue mail in production.
- TOTP replay rejection depends on a shared persistent cache, which is the default `database` store.

## Resolved after the audit

- The original vulnerable lock file was in git history and on `master`. Version 1.0.0 was published to `master` on 2026-11-24, and the same day the public history was rewritten so that only the 2026 modernization commits remain. Pre-2026 commits are no longer in any branch or tag, although GitHub may still serve them by direct commit hash until it purges unreferenced objects. Treat anything that was ever published in that history as compromised.

## Verification limits

No penetration test or third-party audit was performed. Advisory results reflect the databases available on the audit date.
