process.env.NODE_ENV = "production";
var reactScriptsConfig = require("react-scripts/config/webpack.config.prod");
module.exports = Object.assign({}, reactScriptsConfig, {
  entry: './src/index.js',
  output: Object.assign({}, reactScriptsConfig.output, {
    path: __dirname + "/../"
  })
});