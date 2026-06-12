#!/bin/sh
echo "=== Starting Application ==="
echo "PORT=$PORT"

rm -f bootstrap/cache/*.php

echo "=== Running Migrations ==="
php artisan migrate --force || echo "Migration failed, skipping..."

echo "=== Seeding Database if Empty ==="
php artisan tinker --execute="if(\App\Models\User::count() == 0) { \Artisan::call('db:seed', ['--force' => true]); echo \"Database seeded successfully!\n\"; } else { echo \"Database already seeded.\n\"; }"

echo "=== Starting PHP Server on port ${PORT:-8000} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
