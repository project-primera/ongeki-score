import { defineConfig } from 'vite';

/**
 * ブックマークレット本体。
 * 利用者のブックマークに <APP_URL>/bookmarklets/main.js?t=<token> が埋め込まれているため、
 * ハッシュの無い固定ファイル名で、依存をすべて含んだ単一の IIFE として出力する。
 */
export default defineConfig({
    publicDir: false,
    build: {
        outDir: 'public/bookmarklets',
        emptyOutDir: true,
        copyPublicDir: false,
        target: 'es2017',
        sourcemap: false,
        lib: {
            entry: 'resources/assets/js/bookmarklets/getScore.ts',
            formats: ['iife'],
            name: 'OngekiScoreLogBookmarklet',
            fileName: () => 'main.js',
        },
    },
});
