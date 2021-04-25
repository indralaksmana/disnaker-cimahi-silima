const workboxPlugin = require('workbox-webpack-plugin');
let mix = require('laravel-mix');
let tailwindcss = require('tailwindcss');
let SWPrecacheWebpackPlugin = require('sw-precache-webpack-plugin');
require('laravel-mix-purgecss');

if (mix.inProduction()) {
    mix.webpackConfig({
        plugins: [
            new workboxPlugin.InjectManifest({
                swSrc: 'public/sw-offline.js', // more control over the caching
                swDest: 'service-worker.js', // the service-worker file name
                importsDirectory: 'service-worker' // have a dedicated folder for sw files
            })
        ]
    })
}


mix.setPublicPath('./public')
    .js('app/views/assets/js/app.js', 'public/js/app.min.js')
    .sass('app/views/assets/sass-bkol/bkol.scss', 'public/css/bkol.min.css')
    .sass('app/views/assets/sass-admin/dashboard-custom.scss', 'public/css/dashboard-custom.min.css')
    .options({
        processCssUrls: true,
        postCss: [ tailwindcss('./tailwind.js') ],
    })
    .browserSync({
        notify: true,
        proxy: 'silima.test',
        files: [
            './app/views/**/*.twig',
            './public/css/**/*.css',
            './public/js/**/*.js',
        ],
        injectChanges: true,
    })
    .purgeCss({
        globs: [
            path.join(__dirname, "./app/views/**/*.twig"),
            path.join(__dirname, "./app/views/**/*.vue"),
        ],
        extensions: ['html', 'js', 'jsx', 'php', 'vue', 'twig'],
    });
