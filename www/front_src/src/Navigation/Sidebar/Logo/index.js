/* eslint-disable react/jsx-filename-extension */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React, { Component } from 'react';

import clsx from 'clsx';

import logo from '../../../assets/centreon.png';
import styles from './logo.scss';

class Logo extends Component {
  render() {
    const { customClass, onClick } = this.props;

    return (
      <div
        className={clsx(styles.logo, styles[customClass || ''])}
        onClick={onClick}
      >
        <span>
          <img
            alt=""
            className={clsx(styles['logo-image'])}
            height="57"
            src={logo}
            width="254"
          />
        </span>
      </div>
    );
  }
}

export default Logo;
