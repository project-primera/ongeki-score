FROM php:7.3.11-fpm-alpine3.10 AS base
LABEL maintainer "slime-hatena <Slime-hatena@aki-memo.net>"
WORKDIR /app
EXPOSE 80
EXPOSE 443
HEALTHCHECK --start-period=60s --interval=60s --timeout=10s --retries=3 \
    CMD php artisan health:check

FROM composer:1.9.0 AS composer
WORKDIR /src
COPY ./OngekiScoreLog /src
RUN composer config -g repos.packagist composer https://packagist.jp \
    && composer global require hirak/prestissimo \
    && composer install --optimize-autoloader

FROM node:10.16.3-alpine AS node
WORKDIR /src
COPY --from=composer /src /src
RUN yarn install \
    && yarn run production

FROM base AS final
ARG supervisor_version="3.3.5-r0"
ARG nginx_version="1.16.1-r1"
COPY --from=node /src /app
COPY docker/docker-entrypoint.sh /etc/
COPY docker/supervisor/supervisord.conf /etc/
COPY docker/cron/crontabs/root /var/spool/cron/crontabs/root
RUN set -ex \
    && docker-php-ext-install pdo_mysql mysqli >/dev/null \
    && apk add --update-cache --no-cache supervisor=${supervisor_version} nginx=${nginx_version} \
    && mkdir -p /run/nginx \
        && mkdir -p /app/storage/app/log/Debug \
        /app/storage/app/log/Info \
        /app/storage/app/log/Notice \
        /app/storage/app/log/Warning \
        /app/storage/app/log/Error \
        /app/storage/app/log/Critical \
        /app/storage/app/log/Alert \
        /app/storage/app/log/Emergency \
        /app/storage/logs \
    && touch /app/storage/logs/laravel.log \
    && chmod -R 777 /app/storage
ENTRYPOINT [ "/etc/docker-entrypoint.sh" ]
