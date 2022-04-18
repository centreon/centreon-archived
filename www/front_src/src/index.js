/* eslint-disable prefer-arrow-functions/prefer-arrow-functions */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/react-in-jsx-scope */
/* eslint-disable no-multi-assign */
/* eslint-disable func-names */

import { createRoot } from 'react-dom/client';

import Main from './Main';

const container = document.getElementById('root');
const root = createRoot(container);

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
  window.ReactI18Next = await import(
    /* webpackChunkName: "external" */ 'react-i18next'
  );
  window.CentreonUiContext = await import(
    /* webpackChunkName: "external" */ '@centreon/ui-context'
  );
  window.Jotai = await import(/* webpackChunkName: "external" */ 'jotai');

  root.render(<Main />, document.getElementById('root'));
})();
