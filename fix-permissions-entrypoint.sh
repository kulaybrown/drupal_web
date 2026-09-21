#!/bin/sh
set -e

# Named Docker volumes are sometimes created with root ownership even
# though the image content copied into them belongs to www-data, which
# fails Drupal's install-time "is sites/default/files writable" and
# "is settings.php writable" checks. Fix that on every container start,
# before handing off to Apache.

mkdir -p /var/www/html/sites/default/files

chown -R www-data:www-data /var/www/html/sites/default
find /var/www/html/sites/default -type d -exec chmod 775 {} \;
find /var/www/html/sites/default -type f -exec chmod 664 {} \;

exec docker-php-entrypoint "$@"
