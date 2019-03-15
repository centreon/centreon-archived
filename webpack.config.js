const HtmlWebpackPlugin = require("html-webpack-plugin")
const TerserPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const safePostCssParser = require('postcss-safe-parser');

module.exports = {
  context: __dirname,
  entry: [
    "@babel/polyfill",
    "./www/front_src/src/App.scss",
    "./www/front_src/src/index.js"
  ],
  output: {
    path: __dirname + "/www",
    publicPath: '/_CENTREON_PATH_PLACEHOLDER_/',
    filename: 'static/js/[name].[chunkhash:8].js',
    chunkFilename: 'static/js/[name].[chunkhash:8].chunk.js',
    libraryTarget: 'umd',
    library: '[name]',
    umdNamedDefine: true,
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
    new HtmlWebpackPlugin({
      template: './www/front_src/public/index.html',
      filename: 'index.html',
    }),
    new MiniCssExtractPlugin({
      filename: 'static/css/[name].[contenthash:8].css',
      chunkFilename: 'static/css/[name].[contenthash:8].chunk.css',
    }),
  ],
  module: {
    rules: [
      { parser: { system: false } },
      {
        test: /\.js$/,
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
          'css-loader',
          'resolve-url-loader',
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
              outputPath: 'static/fonts/'
            }
        }]
      },
      {
        //test: /Slider\/.+\.(bmp|png|jpg|jpeg|gif|svg)$/,
        test: /@centreon\/react\-components\/.+\.(bmp|png|jpg|jpeg|gif|svg)$/,
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
      {
        test: require.resolve('react'),
        use: [{
          loader: 'expose-loader',
          options: 'React'
        }]
      },
      {
        test: /ReactRouterDom\.js/,
        use: [{
          loader: 'expose-loader',
          options: 'ReactRouterDom'
        }]
      },
      {
        test: /ReactRedux\.js$/,
        use: [{
          loader: 'expose-loader',
          options: 'ReactRedux'
        }]
      },
    ]
  },
};
