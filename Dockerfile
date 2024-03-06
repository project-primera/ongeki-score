FROM php:7.3.33-fpm-alpine3.15 AS base
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
# apk packages.
ARG application_version=""
ARG commit_hash=""
ARG supervisor_version="4.2.2-r2"
ARG nginx_version="1.20.2-r2"
ARG nodejs_version="16.20.2-r0"
ARG npm_version="8.1.3-r0"
# Fix the vulnerability.
ARG curl_version="8.5.0-r0"
ARG busybox_version="1.34.1-r7"
ARG libcurl_version="8.5.0-r0"
ARG zlib_version="1.2.12-r3"
ARG libcrypto1_1_version="1.1.1w-r1"
ARG libretls_version="3.3.4-r3"
ARG libssl1_1_version="1.1.1w-r1"
ARG libxml2_version="2.9.14-r2"
ARG ncurses_libs_version="6.3_p20211120-r2"
ARG ncurses_terminfo_base_version="6.3_p20211120-r2"
ARG nghttp2_libs_version="1.46.0-r2"
ARG openssl_version="1.1.1w-r1"
ARG tar_version="1.34-r1"
ARG xz_version="5.2.5-r1"
ARG xz_libs_version="5.2.5-r1"
# npm package.
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
    && apk add --update-cache --no-cache supervisor=${supervisor_version} \
        nginx=${nginx_version} \
        nodejs=${nodejs_version} \
        npm=${npm_version} \
        curl=${curl_version} \
        busybox=${busybox_version} \
        libcurl=${libcurl_version} \
        zlib=${zlib_version} \
        libcrypto1.1=${libcrypto1_1_version} \
        libretls=${libretls_version} \
        libssl1.1=${libssl1_1_version} \
        libxml2=${libxml2_version} \
        ncurses-libs=${ncurses_libs_version} \
        ncurses-terminfo-base=${ncurses_terminfo_base_version} \
        nghttp2-libs=${nghttp2_libs_version} \
        openssl=${openssl_version} \
        tar=${tar_version} \
        xz=${xz_version} \
        xz-libs=${xz_libs_version} \
    && npm install --global yarn@${npm_yarn_version} \
    && apk del --purge npm \
    && rm -r /root/.npm \
    && docker-php-ext-install pdo_mysql mysqli >/dev/null \
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
