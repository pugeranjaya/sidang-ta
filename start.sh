#!/bin/sh
echo "=== Starting Application ==="
echo "PORT=$PORT"

rm -f bootstrap/cache/*.php

echo "=== Running migrations ==="
php artisan migrate --force 2>&1
echo "=== Migration exit code: $? ==="

echo "=== Starting PHP Server on port ${PORT:-8000} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
