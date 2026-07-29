#!/bin/sh
set -e

php artisan config:clear || true
php artisan migrate --force
php artisan app:seed-if-empty || true

# Start web server first so Render stops returning 502/500 during boot.
php artisan serve --host=0.0.0.0 --port="${PORT:-8000}" &
SERVE_PID=$!

# Background workers (best-effort; never block HTTP)
php artisan queue:work --queue=notifications,default --sleep=3 --tries=4 --backoff=30 --max-time=3600 &
php artisan ai:index-if-empty &

wait "$SERVE_PID"
