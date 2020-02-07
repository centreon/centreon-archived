const HtmlWebpackPlugin = require('html-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');

module.exports = {
  context: __dirname,
  entry: ['@babel/polyfill', './www/front_src/src/index.js'],
  output: {
    path: path.resolve(`${__dirname}/www`),
    publicPath: './',
    filename: 'static/js/[name].[hash:8].js',
    chunkFilename: 'static/js/[name].[hash:8].chunk.js',
    libraryTarget: 'umd',
    library: '[name]',
    umdNamedDefine: true,
  },
  resolve: {
    extensions: ['.ts', '.tsx', '.js', '.jsx', '.scss'],
  },
  optimization: {
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
        test: /\.tsx?$/,
        exclude: /node_modules/,
        use: ['babel-loader', 'awesome-typescript-loader'],
      },
      {
        test: /\.jsx?$/,
        include: [
          path.resolve('./www/front_src/src'),
          /@centreon\/ui/,
          /centreon-ui\/src/,
          path.resolve('./node_modules/@centreon/ui/src'),
        ],
        use: [{ loader: 'babel-loader' }],
      },
      {
        test: /\.(c|sa|sc)ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              modules: {
                localIdentName: '[local]__[hash:base64:5]',
              },
              sourceMap: true,
            },
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
              sourceMap: true,
            },
          },
        ],
      },
      {
        test: /fonts\/.+\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].[hash:8].[ext]',
              outputPath: './static/fonts/',
              publicPath: '../../static/fonts/',
            },
          },
        ],
      },
      {
        test: /\.(bmp|png|jpg|jpeg|gif|svg)$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              limit: 10000,
              name: 'static/img/[name].[hash:8].[ext]',
            },
          },
        ],
      },
    ],
  },
};
