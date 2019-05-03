import React from "react";
import ReactDOM from "react-dom";
import AppProvider from "./Provider";
import { connect as exposedConnect } from "./exposed-libs/ReactRedux.js"; // we add this import to get it in the final bundle
import { Link as ExposedLink } from "./exposed-libs/ReactRouterDom.js"; // we add this import to get it in the final bundle
import * as ReduxForm from "./exposed-libs/ReduxForm.js"; // we add this import to get it in the final bundle
import * as ReactReduxI18n from "react-redux-i18n";

window.ReactReduxI18n = ReactReduxI18n;

ReactDOM.render(<AppProvider />, document.getElementById("root"));
