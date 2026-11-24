# Golf 1.0.0 Release Notes

Version 1.0.0 moves the original 2018 Laravel 5.4 application to a supported stack and adds two-factor authentication. The release has not been tagged or published.

## Highlights

- Laravel 13 on PHP 8.4 or newer, with a Vite and TypeScript frontend
- TOTP two-factor sign-in with single-use recovery codes
- Argon2id password hashing, encrypted sessions and a strict Content Security Policy
- Separate `backend/` and `frontend/` directories
- Automated tests and CI

## Upgrade notes

This is a breaking release. No production deployment is known, and no data migration from the 2018 schema is provided.

- Install dependencies and build assets as described in the README.
- Create a new `.env` from `backend/.env.example`.
- Existing bcrypt hashes remain verifiable only if `HASH_VERIFY=false` is set. They are rehashed on sign-in once that is configured.

## Test results

See [reports/tests/TEST_REPORT.md](reports/tests/TEST_REPORT.md).
