/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/react-in-jsx-scope */
/* eslint-disable no-multi-assign */
/* eslint-disable func-names */

import AppProvider from './Provider';

// make an IIFE function to allow "await" usage
// generate an "external" bundle to embed all needed libraries by external pages and hooks
(async function() {
    window.React = await import(/* webpackChunkName: "external" */ 'react');
    window.ReactDOM = window.ReactDom = await import(/* webpackChunkName: "external" */ 'react-dom');
    window.PropTypes = window.PropTypes = await import(/* webpackChunkName: "external" */ 'prop-types');
    window.ReactRouterDOM = window.ReactRouterDom = await import(/* webpackChunkName: "external" */ 'react-router-dom');
    window.ReactRedux = await import(/* webpackChunkName: "external" */ 'react-redux');
    window.ReduxForm = await import(/* webpackChunkName: "external" */ 'redux-form');
    window.ReactReduxI18n = await import(/* webpackChunkName: "external" */ 'react-redux-i18n');

  window.ReactDOM.render(<AppProvider />, document.getElementById('root'));
})();
