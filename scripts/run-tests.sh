#!/usr/bin/env bash
# Runs both test suites in pinned containers and writes reports/tests.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
php_image="php:8.5-cli@sha256:f324fcb4dc009ac0470a7d614cd122499dcee3ca47a9bab620fc14c0eb759ba2"
node_image="node:24-bookworm-slim@sha256:dc26ae92c9fb15cf6b1d63e20f0944b2efe0f115b20e00be48fb23fceb061c54"
owner="$(id -u):$(id -g)"

mkdir -p "$root/reports/tests"

docker run --rm --network none --user "$owner" -v "$root:/repo" -w /repo/backend "$php_image" \
  sh -c 'php artisan package:discover --no-ansi >/dev/null && vendor/bin/phpunit --no-progress --log-junit ../reports/tests/backend-junit.xml'

docker run --rm --network none --user "$owner" -e HOME=/tmp -v "$root:/repo" -w /repo/frontend "$node_image" \
  sh -c 'npm run typecheck && npm run test:report'

python3 -I "$root/scripts/summarize_test_reports.py" "$root/reports/tests" "$php_image" "$node_image"
