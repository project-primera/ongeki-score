<?php

if (! function_exists('static_asset')) {
    /**
     * ハッシュ無しの固定パスで配信する静的ファイル（/js/*.min.js など）の URL。
     * キャッシュ破棄のためにアプリケーションのバージョンをクエリに付ける（旧 mix() の ?id= 相当）。
     */
    function static_asset(string $path): string
    {
        $version = trim((string) config('env.application-version'), '"');

        return asset($path).($version !== '' ? '?v='.rawurlencode($version) : '');
    }
}
