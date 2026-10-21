# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# 1. フロントエンドのビルド（Vite）。実行イメージには Node を含めない
# ---------------------------------------------------------------------------
FROM node:24-alpine AS assets
ARG application_version=""
ARG commit_hash=""
WORKDIR /src
COPY OngekiScoreLog/package.json OngekiScoreLog/package-lock.json ./
RUN npm ci --no-audit --no-fund --ignore-scripts
COPY OngekiScoreLog/vite.config.js OngekiScoreLog/vite.bookmarklet.config.js OngekiScoreLog/tsconfig.json ./
COPY OngekiScoreLog/resources ./resources
# ブックマークレットに表示するバージョン情報（ビルド時に焼き込む）
ENV VITE_APPLICATION_VERSION=${application_version} \
    VITE_COMMIT_HASH=${commit_hash}
RUN npm run build

# ---------------------------------------------------------------------------
# 2. PHP 依存の解決（実行イメージと同じ PHP で platform 要件を解決する）
# ---------------------------------------------------------------------------
FROM php:8.5-fpm-alpine AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /src
COPY OngekiScoreLog/composer.json OngekiScoreLog/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY OngekiScoreLog /src
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi

# ---------------------------------------------------------------------------
# 3. 実行イメージ: nginx + php-fpm + queue worker + scheduler を非 root で動かす
# ---------------------------------------------------------------------------
FROM php:8.5-fpm-alpine AS final
LABEL maintainer="slime-hatena <Slime-hatena@aki-memo.net>"
ARG application_version=""
ARG commit_hash=""
ARG nginx_version="1.30.4-r1"
ARG supervisor_version="4.3.0-r1"
ENV APPLICATION_VERSION=${application_version} \
    COMMIT_HASH=${commit_hash}
WORKDIR /app

RUN set -ex \
    && apk add --no-cache nginx=${nginx_version} supervisor=${supervisor_version} \
    && docker-php-ext-install pdo_mysql mysqli >/dev/null \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && addgroup -g 1000 app \
    && adduser -D -u 1000 -G app -h /home/app app \
    # nginx を非 root で動かすための書込先
    && mkdir -p /var/lib/nginx/tmp /var/log/nginx /run/nginx \
    && chown -R app:app /var/lib/nginx /var/log/nginx /run/nginx \
    && rm -f /etc/nginx/http.d/default.conf

COPY docker/php/zz-app.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/php/zz-app-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/conf.d/sample.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/docker-entrypoint.sh /etc/docker-entrypoint.sh

COPY --from=vendor --chown=app:app /src /app
COPY --from=assets --chown=app:app /src/public/ /app/public/

RUN set -ex \
    && mkdir -p /app/storage/app/log/Debug \
        /app/storage/app/log/Info \
        /app/storage/app/log/Notice \
        /app/storage/app/log/Warning \
        /app/storage/app/log/Error \
        /app/storage/app/log/Critical \
        /app/storage/app/log/Alert \
        /app/storage/app/log/Emergency \
        /app/storage/framework/cache/data \
        /app/storage/framework/sessions \
        /app/storage/framework/testing \
        /app/storage/framework/views \
        /app/storage/logs \
        /app/bootstrap/cache \
    && touch /app/storage/logs/laravel.log \
    # 起動時に entrypoint（app ユーザー）が /app/.env を作れるようにする（.env 自体はイメージに含めない）
    && chown app:app /app \
    && chown -R app:app /app/storage /app/bootstrap/cache \
    && chmod 755 /etc/docker-entrypoint.sh

USER app
EXPOSE 8080
HEALTHCHECK --start-period=60s --interval=60s --timeout=10s --retries=3 \
    CMD php artisan health:check
ENTRYPOINT [ "/etc/docker-entrypoint.sh" ]
