FROM php:7.3.33-fpm-alpine3.13 AS base
LABEL maintainer "slime-hatena <Slime-hatena@aki-memo.net>"
WORKDIR /app
EXPOSE 80
EXPOSE 443
HEALTHCHECK --start-period=60s --interval=60s --timeout=10s --retries=3 \
    CMD php artisan health:check

FROM composer:1.10.19 AS composer
WORKDIR /src
COPY ./OngekiScoreLog /src
RUN composer install --optimize-autoloader

FROM base AS final
ARG application_version=""
ARG commit_hash=""
ARG supervisor_version="4.2.1-r0"
ARG nginx_version="1.18.0-r15"
ARG nodejs_version="14.19.0-r0"
ARG npm_version="14.19.0-r0"
ARG npm_yarn_version="1.22.17"
COPY --from=composer /src /app
COPY docker/docker-entrypoint.sh /etc/
COPY docker/supervisor/supervisord.conf /etc/
COPY docker/cron/crontabs/root /var/spool/cron/crontabs/root
RUN set -ex \
    && touch /etc/version \
    && echo \"${application_version}\" > /etc/version \
    && touch /etc/hash \
    && echo \"${commit_hash}\" > /etc/hash \
    && docker-php-ext-install pdo_mysql mysqli >/dev/null \
    && apk add --update-cache --no-cache supervisor=${supervisor_version} nginx=${nginx_version} nodejs=${nodejs_version} npm=${npm_version} \
    && npm install --global yarn@${npm_yarn_version} \
    && apk del --purge npm \
    && rm -r /root/.npm \
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
    && chmod -R 777 /app/storage \
    && chmod -R 777 /app/storage/framework/views \
    && chmod -R 777 /app/bootstrap/cache \
    && chmod 777 /etc/docker-entrypoint.sh
ENTRYPOINT [ "/etc/docker-entrypoint.sh" ]
