#!/bin/sh
set -e

echo "=== Berbagive Entrypoint ==="

# Wait for DB/Redis if running in compose
if [ -n "$DB_HOST" ]; then
    echo "Waiting for DB..."
    for i in $(seq 1 30); do
        if php -r "new PDO('pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE:-berbagive}', '${DB_USERNAME:-berbagive}', '${DB_PASSWORD:-berbagive123}');" 2>/dev/null; then
            break
        fi
        sleep 2
    done
fi

echo "Running artisan optimize..."
php artisan optimize --no-interaction 2>/dev/null || true

echo "Warming cache..."
php artisan tinker --execute="
if (DB::connection()->getPdo()) {
    echo 'DB connected' . PHP_EOL;
}
try {
    Cache::store('redis')->get('warmup_check');
    echo 'Redis connected' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Redis: ' . \$e->getMessage() . PHP_EOL;
}
" 2>/dev/null || true

if [ $# -eq 0 ]; then
    echo "Starting PHP-FPM..."
    exec php-fpm
else
    echo "Running: $*"
    exec "$@"
fi
