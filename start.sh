#!/bin/sh
echo "=== Starting Application ==="
echo "PORT=$PORT"
echo "DB_CONNECTION=$DB_CONNECTION"
echo "DATABASE_URL=$DATABASE_URL"

rm -f bootstrap/cache/*.php

echo "=== Running auto-migration ==="
php artisan migrate --force 2>&1
MIGRATE_EXIT=$?
echo "=== Migration exit code: $MIGRATE_EXIT ==="

if [ $MIGRATE_EXIT -ne 0 ]; then
    echo "!!! Migration FAILED. Check DB_CONNECTION and DATABASE_URL env vars !!!"
fi

echo "=== Running db:seed ==="
php artisan db:seed --force 2>&1 || echo "=== Seeding skipped or failed ==="

echo "=== Starting PHP Server on port ${PORT:-8000} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
