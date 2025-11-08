const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css')
    .options({ processCssUrls: false })
    .sourceMaps();

mix.copyDirectory('resources/images', 'public/images');
mix.copyDirectory('node_modules/leaflet-draw/dist/images', 'public/css/images');

if (mix.inProduction()) {
    mix.version();
}
