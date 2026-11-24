# Security Audit

Audit performed in November 2026 during the modernization to version 1.0.0.

## Method

- `gitleaks` over the working tree and full git history (1 commit): no findings
- `composer audit` on the original and updated lock files
- `npm audit` on the new frontend lock file
- Manual review of routes, controllers, views and configuration

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

## Open items

- Registration necessarily reports when an email is already in use.
- Email verification is not enabled.
- Two-factor authentication is optional per account.
- Trusted proxies must be configured per deployment.
- The original vulnerable lock file remains in git history and on `master` until this branch is merged.

## Verification limits

No penetration test or third-party audit was performed. Advisory results reflect the databases available on the audit date.
