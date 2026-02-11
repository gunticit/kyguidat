#!/bin/sh
set -e

# Create required Laravel directories
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/app/public/settings \
         /var/www/html/bootstrap/cache

# Create storage symlink (for serving uploaded files)
rm -f /var/www/html/public/storage
ln -s /var/www/html/storage/app/public /var/www/html/public/storage

# Fix ownership and permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
