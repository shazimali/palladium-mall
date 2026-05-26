#!/bin/bash
set -e

echo "⏳ Waiting for database connection..."

# Load .env file
export $(grep -v '^#' /var/www/palladium_mall/.env | xargs)

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

if [ -z "$APP_KEY" ]; then
    echo "🗝️ Generating APP_KEY..."
    php artisan key:generate --force
else
    echo "✅ APP_KEY already set"
fi

echo "🔁 Running migrations..."
php artisan migrate --force

echo "🔗 Fixing storage link..."
php artisan storage:link --force || true

echo "🚀 Running optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo "✅ Starting Apache..."
exec apache2-foreground