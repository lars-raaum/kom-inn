/* eslint strict: 0 */
'use strict';

const path = require('path');

const DefinePlugin = require('webpack/lib/DefinePlugin');

const autoprefixer = require('autoprefixer');

const env = process.env.NODE_ENV || 'production';
const isProd = (env === 'production');

// Which browsers do we want autoprefixer to support?
const browserSupport = [
    // IE9 and up
    'ie >= 9',
    // All browsers with more than 1% usage
    '> 1%',
    // Last three versions of every browser
    'last 3 versions'
];

var webpackConfig = {
    devtool: 'cheap-module-source-map',
    entry: ['./src/js/app.js', './src/scss/main.scss'],
    output: {
        path: path.join(__dirname, 'public', 'js'),
        filename: 'app.js',
        publicPath: '/js'
    },
    resolve: {
        root: [
            path.resolve('./src/js'),
            path.resolve('./src/sass')
        ],
        extensions: ['', '.js', '.jsx', '.scss']
    },
    module: {
        loaders: [
            {
                test: /\.js$/,
                loaders: ['babel?cacheDirectory'],
                include: path.join(__dirname, 'src', 'js'),
                exclude: /node_modules/
            },
            {
                test: /\.scss$/,
                include: path.join(__dirname, 'src', 'scss'),
                loaders: ['style', 'css?sourceMap&-autoprefixer', 'postcss', 'sass?sourceMap']
            },
            { test: /\.json$/, loader: "json-loader" },
            { test: /\.(png|jpg|jpeg)$/, loader: "url-loader?limit=10000000" }
        ]
    },
    postcss: [ autoprefixer({ browsers: browserSupport }) ],
    plugins: [
        new DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify(env)
            }
        })
    ],
    node: {
      fs: "empty"
    }
};

if (isProd) {
    const DedupePlugin = require('webpack/lib/optimize/DedupePlugin');
    const UglifyJsPlugin = require('webpack/lib/optimize/UglifyJsPlugin');

    webpackConfig.plugins.push(new DedupePlugin());
    /* eslint-disable */
    webpackConfig.plugins.push(
        new UglifyJsPlugin({
            sourceMap: false,
            compressor: {
                warnings: false
            }
        })
    );
    /* eslint-enable */
} else {
    const NoErrorsPlugin = require('webpack/lib/NoErrorsPlugin');
    const HotModuleReplacementPlugin = require('webpack/lib/HotModuleReplacementPlugin');
    const OccurenceOrderPlugin = require('webpack/lib/optimize/OccurenceOrderPlugin');

    webpackConfig.devtool = 'cheap-module-eval-source-map';
    webpackConfig.plugins.push(new OccurenceOrderPlugin());
    webpackConfig.plugins.push(new HotModuleReplacementPlugin());
    webpackConfig.plugins.push(new NoErrorsPlugin());
    webpackConfig.devServer = {
        hot: true,
        port: 9000,
        contentBase: 'public/',
        proxy: {
            '/api': {
                target: process.env.API_URL,
                pathRewrite: {'^/api' : ''}
            }
        },
        historyApiFallback: {
            index: 'index.html'
        }
    }
}

module.exports = webpackConfig;
