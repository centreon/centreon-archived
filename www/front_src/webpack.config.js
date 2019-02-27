const HtmlWebpackPlugin = require("html-webpack-plugin")
const TerserPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
  context: __dirname,
  entry: [
    "@babel/polyfill",
    "./src/App.scss",
    "./src/index.js"
  ],
  output: {
    path: __dirname + "/..",
    filename: 'static/js/[name].[chunkhash:8].js',
    chunkFilename: 'static/js/[name].[chunkhash:8].chunk.js',
    libraryTarget: 'umd',
    library: '[name]',
    umdNamedDefine: true,
  },
  optimization: {
    minimizer: [new TerserPlugin()],
  },
  plugins: [
    new HtmlWebpackPlugin({
      template: './index.html',
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
        exclude: /(node_modules)/,
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
        test: [/\.bmp$/, /\.gif$/, /\.jpe?g$/, /\.png$/, /\.svg$/],
        loader: require.resolve('url-loader'),
        options: {
          limit: 10000,
          name: 'static/img/[name].[hash:8].[ext]',
        },
      },
      {
        test: /\/fonts\//,
        loader: 'file-loader',
        exclude: [/\.(js|jsx)$/, /\.(c|sa|sc)ss$/, /\.html$/, /\.json$/],
        options: {
          publicPath: '../../',
          name: 'static/fonts/[name].[hash:8].[ext]',
        },
      },
      {
        test: /\/img\//,
        loader: 'file-loader',
        exclude: [/\.(js|jsx)$/, /\.(c|sa|sc)ss$/, /\.html$/, /\.json$/],
        options: {
          publicPath: '../../',
          name: 'static/img/[name].[hash:8].[ext]',
        },
      },
    ]
  },
};
