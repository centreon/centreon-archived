/* eslint-disable prefer-arrow-functions/prefer-arrow-functions */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/react-in-jsx-scope */
/* eslint-disable no-multi-assign */
/* eslint-disable func-names */

import AppProvider from './App';

// make an IIFE function to allow "await" usage
// generate an "external" bundle to embed all needed libraries by external pages and hooks
(async function () {
  window.React = await import(/* webpackChunkName: "external" */ 'react');
  window.ReactDOM = window.ReactDom = await import(
    /* webpackChunkName: "external" */ 'react-dom'
  );
  window.PropTypes = window.PropTypes = await import(
    /* webpackChunkName: "external" */ 'prop-types'
  );
  window.ReactRouterDOM = window.ReactRouterDom = await import(
    /* webpackChunkName: "external" */ 'react-router-dom'
  );
  window.ReactRedux = await import(
    /* webpackChunkName: "external" */ 'react-redux'
  );
  window.ReduxForm = await import(
    /* webpackChunkName: "external" */ 'redux-form'
  );
  window.ReactI18Next = await import(
    /* webpackChunkName: "external" */ 'react-i18next'
  );
  window.CentreonUiContext = await import(
    /* webpackChunkName: "external" */ '@centreon/ui-context'
  );

  window.ReactDOM.render(<AppProvider />, document.getElementById('root'));
})();
