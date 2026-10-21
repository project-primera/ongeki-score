import { copyFileSync, cpSync, mkdirSync } from 'node:fs';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

/**
 * ハッシュ無しの固定パスで配信する静的ファイルをコピーする。
 * - 外部ライブラリ（list.js など）は view から従来どおり /js/*.min.js で読み込む（グローバル変数に依存するため ES module にしない）
 * - 画像は /img/、favicon 類は / 直下
 */
function copyStaticAssets() {
    const vendorScripts = {
        'node_modules/list.js/dist/list.min.js': 'public/js/list.min.js',
        'node_modules/sweet-scroll/sweet-scroll.min.js': 'public/js/sweet-scroll.min.js',
        'node_modules/html2canvas/dist/html2canvas.min.js': 'public/js/html2canvas.min.js',
        'node_modules/clipboard/dist/clipboard.min.js': 'public/js/clipboard.min.js',
    };

    return {
        name: 'ongeki-copy-static-assets',
        apply: 'build',
        closeBundle() {
            mkdirSync('public/js', { recursive: true });
            for (const [from, to] of Object.entries(vendorScripts)) {
                copyFileSync(from, to);
            }
            cpSync('resources/assets/img', 'public/img', { recursive: true });
            cpSync('resources/assets/favicons', 'public', { recursive: true });
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/sass/style.scss',
                'resources/assets/js/app.js',
                'resources/assets/js/sortTable.js',
                'resources/assets/js/tableScalable.js',
                'resources/assets/js/sortAllUserTable.js',
                'resources/assets/js/sortMusic.js',
                'resources/assets/js/sortTrophy.js',
                'resources/assets/js/changeGraphSize.js',
                'resources/assets/js/userProgress.ts',
            ],
            refresh: true,
        }),
        copyStaticAssets(),
    ],
});
