//import React from "react";
//import ReactDOM from "react-dom";
import AppProvider from "./Provider";
//import { connect as exposedConnect } from "./exposed-libs/ReactRedux.js"; // we add this import to get it in the final bundle
//import { Link as ExposedLink } from "./exposed-libs/ReactRouterDom.js"; // we add this import to get it in the final bundle
//import * as ReduxForm from "./exposed-libs/ReduxForm.js"; // we add this import to get it in the final bundle
//import * as ReactReduxI18n from "react-redux-i18n";


(async function() {
    window.React = await import(/* webpackChunkName: "external" */ 'react');
    window.ReactDOM = await import(/* webpackChunkName: "external" */ 'react-dom');
    window.ReactRouterDOM = await import(/* webpackChunkName: "external" */ 'react-router-dom');
    window.ReactRedux = await import(/* webpackChunkName: "external" */ 'react-redux');
    window.ReduxForm = await import(/* webpackChunkName: "external" */ 'redux-form');
    window.ReactReduxI18n = await import(/* webpackChunkName: "external" */ 'react-redux-i18n');

    window.ReactDOM.render(<AppProvider />, document.getElementById("root"));
})();

