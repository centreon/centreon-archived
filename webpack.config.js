const HtmlWebpackPlugin = require('html-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const safePostCssParser = require('postcss-safe-parser');
const path = require('path');

module.exports = {
  context: __dirname,
  entry: [
    "@babel/polyfill",
    "./www/front_src/src/index.js"
  ],
  output: {
    path: path.resolve(__dirname + '/www'),
    publicPath: './',
    filename: 'static/js/[name].[hash:8].js',
    chunkFilename: 'static/js/[name].[hash:8].chunk.js',
    libraryTarget: 'umd',
    library: '[name]',
    umdNamedDefine: true,
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  optimization: {
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          parse: {
            ecma: 8,
          },
          compress: {
            ecma: 5,
            warnings: false,
            comparisons: false,
          },
          mangle: {
            safari10: true,
          },
          output: {
            ecma: 5,
            comments: false,
            ascii_only: true,
          },
        },
        parallel: true,
        cache: true,
        sourceMap: true,
      }),
      new OptimizeCSSAssetsPlugin({
        cssProcessorOptions: {
          parser: safePostCssParser,
          map: {
            inline: false,
            annotation: true,
          },
        },
      }),
    ],
    splitChunks: {
      chunks: 'all',
    },
    runtimeChunk: true,
  },
  plugins: [
    new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: ['static/**/*'],
    }),
    new HtmlWebpackPlugin({
      template: './www/front_src/public/index.html',
      filename: 'index.html',
    }),
    new MiniCssExtractPlugin({
      publicPath: './',
      filename: 'static/css/[name].[contenthash:8].css',
      chunkFilename: 'static/css/[name].[contenthash:8].chunk.css',
    }),
  ],
  module: {
    rules: [
      { parser: { system: false } },
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            babelrc: true
          },
        }
      },
      {
        test: /\.(c|sa|sc)ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: "css-loader",
            options: {
              modules: {
                localIdentName: "[local]__[hash:base64:5]",
              },
              sourceMap: true,
            }
          },
          {
            loader: 'resolve-url-loader',
            options: {
              sourceMap: true,
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true
            }
          },
        ],
      },
      {
        test: /fonts\/.+\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
        use: [{
            loader: 'file-loader',
            options: {
              name: '[name].[ext]',
              outputPath: './static/fonts/',
              publicPath: '../../static/fonts/'
            }
        }]
      },
      {
        test: /@centreon\/react\-components\/lib\/.+\.(bmp|png|jpg|jpeg|gif|svg)$/,
        use: [{
          loader: 'url-loader',
          options: {
            limit: 10000,
            name: 'static/img/[name].[hash:8].[ext]',
          },
        }]
      },
      {
        test: /img\/.+\.(bmp|png|jpg|jpeg|gif|svg)$/,
        use: [{
          loader: 'url-loader',
          options: {
            limit: 10000,
            name: 'static/img/[name].[hash:8].[ext]',
          },
        }]
      },
    ]
  },
};
