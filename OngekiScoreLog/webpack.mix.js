let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
    .js('resources/assets/js/sortTable.js', 'public/js')
    .js('resources/assets/js/tableScalable.js', 'public/js')
    .js('resources/assets/js/sortAllUserTable.js', 'public/js')
    .js('resources/assets/js/sortMusic.js', 'public/js')
    .js('resources/assets/js/sortTrophy.js', 'public/js')
    .js('resources/assets/js/userProgress.js', 'public/js')
    .sass('resources/assets/sass/laravel/app.scss', 'public/css')
    .sass('resources/assets/sass/style.scss', 'public/css')
    .copy('node_modules/list.js/dist/list.min.js', 'public/js/list.min.js')
    .copy('node_modules/sweet-scroll/sweet-scroll.min.js', 'public/js/sweet-scroll.min.js')
    .copy('node_modules/html2canvas/dist/html2canvas.min.js', 'public/js/html2canvas.min.js')
    .copy('../Bookmarklet/bin/main.js', 'public/bookmarklets/main.js')
    .copyDirectory('resources/assets/img', 'public/img')
    .copyDirectory('resources/assets/favicons', 'public')

if (mix.inProduction()) {
    mix.version();
}else{
    mix.sourceMaps()
    .disableNotifications();
}