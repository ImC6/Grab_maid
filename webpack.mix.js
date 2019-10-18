const mix = require('laravel-mix');

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

mix.setResourceRoot('../')
    .setPublicPath('public')
    .copy('resources/images', 'public/images', false)
    .react('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .extract(['bootstrap', 'react', 'react-dom', 'lodash', 'axios', 'jquery', 'moment'])
    .version();

// if (mix.inProduction()) {
//     mix.version();
// }
