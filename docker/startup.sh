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

if [ "${CONTAINER_ROLE:-app}" = "app" ]; then
    if [ -z "$APP_KEY" ]; then
        echo "🗝️ Generating APP_KEY..."
        php artisan key:generate --force
    else
        echo "✅ APP_KEY already set"
    fi

    echo "🔁 Running migrations..."
    php artisan migrate --force

    echo "🔗 Fixing storage link..."
    mkdir -p /var/www/palladium_mall/storage/framework/views
    mkdir -p /var/www/palladium_mall/storage/framework/cache
    mkdir -p /var/www/palladium_mall/storage/framework/sessions
    php artisan storage:link --force || true

    echo "🚀 Running optimizations..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    php artisan event:cache || true

    echo "✅ Starting Apache..."
    exec apache2-foreground
elif [ "$CONTAINER_ROLE" = "queue" ]; then
    echo "🚀 Starting queue worker..."
    exec php artisan queue:work --verbose --tries=3 --timeout=90
elif [ "$CONTAINER_ROLE" = "scheduler" ]; then
    echo "🚀 Starting scheduler..."
    exec php artisan schedule:work
else
    echo "❌ Unknown CONTAINER_ROLE: $CONTAINER_ROLE"
    exit 1
fi