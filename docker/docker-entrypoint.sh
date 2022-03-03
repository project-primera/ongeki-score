#!/bin/sh

cp /etc/.env /app/.env

echo -n "APPLICATION_VERSION=" | cat - /etc/version >> ~/.profile
echo -n "MIX_APPLICATION_VERSION=" | cat - /etc/version >> ~/.profile
echo -n "COMMIT_HASH=" | cat - /etc/hash >> ~/.profile
echo -n "MIX_COMMIT_HASH=" | cat - /etc/hash >> ~/.profile

yarn install
yarn run production

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
