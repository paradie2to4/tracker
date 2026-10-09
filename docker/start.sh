#!/bin/sh
# Container entrypoint: runs once per deploy/restart, then hands off to Apache.
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generate one locally with 'php artisan key:generate --show' and add it in Render." >&2
    exit 1
fi

# Remove apache.conf's default "Listen 80" so only ${PORT} is bound.
sed -i 's/^Listen 80$//' /etc/apache2/ports.conf

# Cache config, routes, views and events now that environment variables exist.
php artisan optimize

# The cache files above were written as root; Apache runs as www-data.
chown -R www-data:www-data storage bootstrap/cache

# Apply pending migrations. --force is required in production.
# With a single web instance there is no risk of two containers migrating at once.
php artisan migrate --force

# Load the demo supply chain once (does nothing if it is already there).
if [ "${SEED_DEMO_DATA:-false}" = "true" ]; then
    php artisan app:seed-demo
fi

exec apache2-foreground
