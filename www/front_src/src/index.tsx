/* eslint-disable @typescript-eslint/explicit-function-return-type */
/* eslint-disable prefer-arrow-functions/prefer-arrow-functions */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/react-in-jsx-scope */
/* eslint-disable no-multi-assign */
/* eslint-disable func-names */

import React from 'react';

import Main from './Main';

declare global {
  interface Window {
    CentreonUiContext;
    Jotai;
    React;
    ReactDom;
    ReactI18Next;
    ReactRouterDOM;
    ReactRouterDom;
  }
}

// make an IIFE function to allow "await" usage
// generate an "external" bundle to embed all needed libraries by external pages and hooks
(async function () {
  window.React = await import(/* webpackChunkName: "external" */ 'react');
  window.ReactDOM = window.ReactDom = await import(
    /* webpackChunkName: "external" */ 'react-dom'
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

  window.ReactDOM.render(<Main />, document.getElementById('root'));
})();
