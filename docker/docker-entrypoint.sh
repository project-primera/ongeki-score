#!/bin/sh

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Waiting for mysql."
COMMAND_STATUS=1
until [ $COMMAND_STATUS -eq 0 ];do
  sleep 10
  php /app/artisan migrate --force
  COMMAND_STATUS=$?
done

supervisord --configuration /etc/supervisord.conf
