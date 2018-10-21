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

mix.sourceMaps()
    .js('resources/assets/js/app.js', 'public/js')
    .js('resources/assets/js/sortTable.js', 'public/js')
    .js('resources/assets/js/sortAllUserTable.js', 'public/js')
    .sass('resources/assets/sass/laravel/app.scss', 'public/css')
    .sass('resources/assets/sass/style.scss', 'public/css');   

mix.copy('node_modules/list.js/dist/list.min.js', 'public/js/list.min.js');