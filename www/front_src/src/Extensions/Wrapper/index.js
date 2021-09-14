/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React, { Component } from 'react';

import clsx from 'clsx';

import styles from '../../global-sass-files/_containers.scss';

class ExtensionsWrapper extends Component {
  render() {
    const { children, style } = this.props;

    return (
      <div className={clsx(styles['content-wrapper'])} style={style}>
        {children}
      </div>
    );
  }
}

export default ExtensionsWrapper;
