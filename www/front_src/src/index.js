import React from "react";
import ReactDOM from "react-dom";
import "loaders.css/loaders.min.css";
import AppProvider from "./Provider";
import { connect as exposedConnect } from "./exposed-libs/ReactRedux.js"; // we add this import to get it in the final bundle
import { Link as ExposedLink } from "./exposed-libs/ReactRouterDom.js"; // we add this import to get it in the final bundle

ReactDOM.render(<AppProvider />, document.getElementById("root"));
