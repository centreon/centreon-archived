/* eslint-disable prefer-arrow-functions/prefer-arrow-functions */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/react-in-jsx-scope */
/* eslint-disable no-multi-assign */
/* eslint-disable func-names */

import React from 'react';

import ReactDOM from 'react-dom';

import AppProvider from './Provider';

ReactDOM.render(
  <React.StrictMode>
    <AppProvider />
  </React.StrictMode>,
  document.getElementById('root'),
);
