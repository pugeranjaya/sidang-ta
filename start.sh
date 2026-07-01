#!/bin/sh
echo "=== Starting Application ==="
echo "PORT=$PORT"

rm -f bootstrap/cache/*.php

echo "=== Running auto-migration ==="
php artisan migrate --force
echo "=== Starting PHP Server on port ${PORT:-8000} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
