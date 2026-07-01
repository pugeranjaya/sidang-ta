#!/bin/sh
echo "=== Starting Application ==="
echo "PORT=$PORT"

rm -f bootstrap/cache/*.php

echo "=== Running migrate:fresh ==="
php artisan migrate:fresh --force 2>&1
MIGRATE_EXIT=$?
echo "=== Migration exit code: $MIGRATE_EXIT ==="

if [ $MIGRATE_EXIT -ne 0 ]; then
    echo "!!! Migration FAILED !!!"
else
    echo "=== Running db:seed ==="
    php artisan db:seed --force 2>&1
    echo "=== Seed exit code: $? ==="
fi

echo "=== Starting PHP Server on port ${PORT:-8000} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
