#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

expected_dir="/var/www/hexaterminal"
output_file="docs/baseline/evidence/production-environment-output.txt"
output_dir="$(dirname "$output_file")"

if [[ "$(pwd -P)" != "$expected_dir" ]]; then
  echo "This script must be run from $expected_dir on the production server." >&2
  exit 1
fi

if [[ ! -f artisan ]]; then
  echo "artisan was not found in the current directory." >&2
  exit 1
fi

if [[ ! -d "$output_dir" ]]; then
  echo "Output directory does not exist: $output_dir" >&2
  exit 1
fi

if [[ -L "$output_file" ]]; then
  echo "Refusing to overwrite a symbolic link: $output_file" >&2
  exit 1
fi

# Capture Git state before creating or truncating the evidence file, so the
# collector's own output does not contaminate the recorded working-tree status.
git_branch="$(git branch --show-current)"
git_commit="$(git rev-parse HEAD)"
git_status="$(git status --short)"

laravel_config_json="$(
php <<'PHP'
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$payload = [
    'APP_ENV' => config('app.env'),
    'APP_DEBUG' => config('app.debug'),
    'database.default' => config('database.default'),
    'cache.default' => config('cache.default'),
    'queue.default' => config('queue.default'),
    'session.driver' => config('session.driver'),
    'mail.default' => config('mail.default'),
    'filesystems.default' => config('filesystems.default'),
    'logging.default' => config('logging.default'),
];

echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
)"

nginx_version="$(nginx -v 2>&1 || true)"
nginx_test_output="$(nginx -t 2>&1 || true)"
frontend_state="$(systemctl show hexa-frontend --property=ActiveState --property=SubState --property=FragmentPath --no-pager 2>&1 || true)"

curl_status() {
  local url="$1"
  curl \
    --silent \
    --show-error \
    --output /dev/null \
    --write-out '%{http_code}' \
    --retry 3 \
    --retry-delay 2 \
    --retry-connrefused \
    --connect-timeout 10 \
    --max-time 30 \
    "$url" 2>/dev/null || printf '000'
}

backend_status="$(curl_status 'https://api.hexaterminal.com/api/health/ready')"
frontend_status="$(curl_status 'https://hexaterminal.com/api/health')"
public_page_status="$(curl_status 'https://hexaterminal.com/en')"

{
  echo "Timestamp: $(date -Is)"
  echo "Hostname: $(hostname)"
  echo "Operating system summary: $(uname -srvmo)"
  echo "Current directory: $(pwd -P)"
  echo
  echo "Repository identity"
  echo "Branch: $git_branch"
  echo "Commit: $git_commit"
  echo "Working tree status:"
  if [[ -n "$git_status" ]]; then
    echo "$git_status"
  else
    echo "CLEAN"
  fi
  echo
  echo "Runtime versions"
  php --version | sed -n '1,2p'
  composer --version
  php artisan --version
  node --version
  npm --version
  echo
  echo "Nginx"
  echo "$nginx_version"
  echo "$nginx_test_output"
  echo
  echo "hexa-frontend service"
  echo "$frontend_state"
  echo
  echo "Whitelisted Laravel configuration"
  echo "$laravel_config_json"
  echo
  echo "HTTP checks"
  echo "Backend readiness status: $backend_status"
  echo "Frontend health status: $frontend_status"
  echo "Public English page status: $public_page_status"
  echo
  echo "Known log locations and safe commands"
  echo "Laravel log path: storage/logs/laravel.log"
  echo 'Frontend journal command: journalctl -u hexa-frontend --since "30 minutes ago" --no-pager'
  echo 'Laravel tail command: tail -n 100 storage/logs/laravel.log'
} > "$output_file"

echo "Saved production baseline evidence to $output_file"
