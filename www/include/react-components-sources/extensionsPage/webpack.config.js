process.env.NODE_ENV = "production";
var reactScriptsConfig = require("react-scripts/config/webpack.config.prod");
const { DefinePlugin } = require("webpack");

reactScriptsConfig.plugins.push(
  new DefinePlugin({
    "process.env.COMPONENT_SOURCE_PATH": JSON.stringify(
      process.env.COMPONENT_SOURCE_PATH
    ),
    "process.env.COMPONENT_NAME": JSON.stringify(process.env.COMPONENT_NAME)
  })
);

module.exports = Object.assign({}, reactScriptsConfig, {
  entry: ["@babel/polyfill", __dirname + "/src/index.js"],
  output: Object.assign({}, reactScriptsConfig.output, {
    path:
      __dirname + process.env.COMPONENT_BUILD_PATH + process.env.COMPONENT_NAME
  })
});
