#!/bin/bash
set -e

# Fix storage permissions on every container start
# (needed because Docker volumes may reset ownership)
mkdir -p /var/www/html/storage/uploads/thumbnails
chown -R www-data:www-data /var/www/html/storage/
chmod -R 775 /var/www/html/storage/

# Execute the main command (apache2-foreground)
exec "$@"
