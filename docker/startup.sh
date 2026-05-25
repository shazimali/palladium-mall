#!/bin/bash
set -e

echo "⏳ Waiting for database connection..."

# Use a raw TCP check — works on fresh databases too
until php -r "
  \$dsn = 'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 3306);
  try {
    new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    exit(0);
  } catch (Exception \$e) {
    exit(1);
  }
" 2>/dev/null; do
    echo "  Database not ready, retrying in 3s..."
    sleep 3
done

echo "✅ Database ready"

# Generate APP_KEY only if truly missing from environment
if [ -z "$APP_KEY" ]; then
    echo "🗝️ Generating APP_KEY..."
    php artisan key:generate --force
else
    echo "✅ APP_KEY already set"
fi

# Run migrations — fail loudly on error
echo "🔁 Running migrations..."
php artisan migrate --force

# Fix storage symlink (idempotent)
echo "🔗 Fixing storage link..."
php artisan storage:link --force || true

# Laravel optimizations
echo "🚀 Running optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo "✅ Starting Apache..."
exec apache2-foreground