/* eslint strict: 0 */
'use strict';

const path = require('path');

const webpack = require('webpack');
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

let webpackConfig = {
    devtool: 'source-map',
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
                loaders: ['babel'],
                include: path.join(__dirname, 'src', 'js'),
                exclude: /node_modules/
            },
            {
                test: /\.scss$/,
                include: path.join(__dirname, 'src', 'scss'),
                loaders: ['style', 'css?sourceMap&-autoprefixer', 'postcss', 'sass?sourceMap']
            },
            { test: /\.json$/, loader: "json-loader" }
        ]
    },
    postcss: [ autoprefixer({ browsers: browserSupport }) ],
    plugins: [
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify(env),
                API_URL: JSON.stringify(process.env.API_URL)
            }
        })
    ],
    node: {
        fs: 'empty'
    }
};

if (isProd) {
    webpackConfig.plugins.push(new webpack.optimize.DedupePlugin());
    /* eslint-disable */
    webpackConfig.plugins.push(
        new webpack.optimize.UglifyJsPlugin({
            compressor: {
                screw_ie8: true,
                warnings: false
            }
        })
    );
    /* eslint-enable */
} else {
    webpackConfig.devtool = 'eval-source-map';
    webpackConfig.plugins.push(new webpack.optimize.OccurenceOrderPlugin());
    webpackConfig.plugins.push(new webpack.HotModuleReplacementPlugin());
    webpackConfig.plugins.push(new webpack.NoErrorsPlugin());
    webpackConfig.devServer = {
        hot: true,
        inline: true,
        port: 7000,
        contentBase: 'public/',
        proxy: {
            '/api': {
                target: process.env.API_URL
            }
        }
    }
}

module.exports = webpackConfig;
