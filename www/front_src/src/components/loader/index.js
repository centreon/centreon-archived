/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prefer-stateless-function */

import React, { Component } from 'react';
import classnames from 'classnames';
import loaderStyles from 'loaders.css/loaders.min.css';
import styles from './loader.scss';

class Loader extends Component {
  render() {
    return (
      <div className={styles.loader}>
        <div
          className={classnames(
            styles['loader-inner'],
            loaderStyles['ball-grid-pulse'],
          )}
        >
          <div />
          <div />
          <div />
          <div />
        </div>
      </div>
    );
  }
}

export default Loader;
