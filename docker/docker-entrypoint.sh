#!/bin/sh

cp /etc/.env /app/.env


sed -i '/^APPLICATION_VERSION/d' /app/.env
sed -i '/^MIX_APPLICATION_VERSION/d' /app/.env
sed -i '/^COMMIT_HASH/d' /app/.env
sed -i '/^MIX_COMMIT_HASH/d' /app/.env
echo -n "APPLICATION_VERSION=" | cat - /etc/version >> /app/.env
echo "MIX_APPLICATION_VERSION=\"\${APPLICATION_VERSION}\"" >> /app/.env
echo -n "COMMIT_HASH=" | cat - /etc/hash >> /app/.env
echo "MIX_COMMIT_HASH=\"\${COMMIT_HASH}\"" >> /app/.env

echo -n "export APPLICATION_VERSION=" | cat - /etc/version >> ~/.profile
echo -n "export MIX_APPLICATION_VERSION=" | cat - /etc/version >> ~/.profile
echo -n "export COMMIT_HASH=" | cat - /etc/hash >> ~/.profile
echo -n "export MIX_COMMIT_HASH=" | cat - /etc/hash >> ~/.profile

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
