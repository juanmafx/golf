# Changelog

This project follows [Semantic Versioning](https://semver.org/).

## [1.0.0] - Unreleased

First versioned release. The repository had no earlier tags.

### Added

- TOTP two-factor authentication with recovery codes
- Security headers with a nonce-based Content Security Policy
- Rate limits for authentication endpoints
- PHPUnit and Vitest suites with a committed test report
- GitHub Actions CI with least-privilege permissions and pinned actions
- MySQL schema, architecture, security and third-party notice documents

### Changed

- **Breaking:** upgraded from Laravel 5.4 to Laravel 13, requiring PHP 8.4 or newer
- **Breaking:** moved the application into `backend/` and frontend source into `frontend/`
- **Breaking:** new passwords require at least 12 characters
- Replaced the built-in auth controllers with Laravel Fortify
- Replaced Laravel Mix, Vue, jQuery and Bootstrap with Vite, TypeScript and CSS
- Changed password hashing to Argon2id
- Translated the remaining Spanish interface text to English

### Removed

- Non-functional `/api/user` route
- Unused example Vue component, broadcast channel and `inspire` command
- Tinker
- Committed compiled assets

### Security

- Resolved all 42 Composer advisories reported against the original lock file
