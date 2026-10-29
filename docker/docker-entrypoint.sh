#!/bin/sh
# コンテナ起動時の初期化。非 root（app ユーザー）で実行される。
set -eu

# /etc/.env（マウント）を /app/.env へ。バージョン情報はビルド時の値で上書きする。
# /app ディレクトリは app 所有（中身は root 所有）なので、/app/.env は app ユーザーが作成できる。
# .env が無い・読めない・空（バージョン行しか無い場合も含む）なら、理由を出して起動を止める。
# 設定無しのまま起動して後で分かりにくく落ちるより、ここで止める方が原因を追いやすい
if [ ! -r /etc/.env ] || ! grep -q -v -e '^APPLICATION_VERSION=' -e '^COMMIT_HASH=' /etc/.env; then
    echo "ERROR: /etc/.env が無いか空です。アプリの .env を /etc/.env にマウントしてください。" >&2
    exit 1
fi
# 値は二重引用符で囲む。囲まないと空白や # を含む値（例: ローカルビルドの "Local build."）で .env の読み込みに失敗して起動できない
{
    grep -v -e '^APPLICATION_VERSION=' -e '^COMMIT_HASH=' /etc/.env
    printf 'APPLICATION_VERSION="%s"\nCOMMIT_HASH="%s"\n' "${APPLICATION_VERSION:-}" "${COMMIT_HASH:-}"
} > /app/.env

# oauth 鍵は /etc/oauth/ にマウントされていれば storage/ へ複製する（所有者 app、600）。
# /app/storage/ に直接マウントする場合は app ユーザー（uid 1000）が読めるようにしておく
for key in oauth-private.key oauth-public.key; do
    if [ -f "/etc/oauth/${key}" ]; then
        cat "/etc/oauth/${key}" > "/app/storage/${key}"
        chmod 600 "/app/storage/${key}"
    fi
    # Passport は鍵ファイルの権限が 400/440/600/640/660 でないと例外を投げる（API が全て 500 になる）。
    # 直接マウントされた鍵はここで検査し、直せなければ起動を止める
    if [ -f "/app/storage/${key}" ]; then
        chmod 600 "/app/storage/${key}" 2>/dev/null || true
        mode=$(stat -c %a "/app/storage/${key}")
        case "$mode" in
            400|440|600|640|660) ;;
            *) echo "ERROR: /app/storage/${key} の権限が ${mode} です。所有者を uid 1000 にして 600 にするか、/etc/oauth/ にマウントしてください。" >&2; exit 1 ;;
        esac
        if [ ! -r "/app/storage/${key}" ]; then
            echo "ERROR: /app/storage/${key} を app ユーザー（uid 1000）が読めません。" >&2; exit 1
        fi
    fi
done

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Waiting for mysql."
until php /app/artisan migrate --force; do
    sleep 10
done

exec supervisord --configuration /etc/supervisord.conf
