#!/bin/bash
set -e

echo "⏳ Waiting for database connection..."

until php artisan migrate:status > /dev/null 2>&1; do
    echo "Waiting for database..."
    sleep 3
done

echo "✅ Database ready"

# Generate APP_KEY if missing
if ! grep -q "APP_KEY=base64:" /var/www/palladium_mall/.env; then
    echo "🗝️ Generating APP_KEY..."
    php artisan key:generate --force
else
    echo "✅ APP_KEY already set"
fi

# Run pending migrations
echo "🔁 Running migrations..."
php artisan migrate --force || true

# Fix storage symlink
echo "🔗 Fixing storage link..."
php artisan storage:link || true

# Laravel optimizations
echo "🚀 Running Laravel optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo "✅ Starting Apache server..."
apache2-foreground