let mix = require('laravel-mix');
let CompressionWebpackPlugin = require('compression-webpack-plugin');

let webpackConfig = {
    output: {
        publicPath: '/',
        filename: '[name].js'
    },
    plugins: [],
};
if (mix.inProduction()) {
    webpackConfig.output.chunkFilename = '[name].js?id=[chunkhash:20]';
    webpackConfig.plugins.push(new CompressionWebpackPlugin({
        filename: '[path].gz[query]',
        algorithm: 'gzip',
        test: new RegExp(
            '\\.(' +
            ['js', 'css'].join('|') +
            ')$'
        ),
        threshold: 10240,
        minRatio: 0.8
    }));
}
mix.webpackConfig(webpackConfig);
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

mix.js('resources/assets/js/money-online.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css');

mix.extract(['mint-ui', 'vue']);
if (mix.inProduction) {
    mix.version();
}