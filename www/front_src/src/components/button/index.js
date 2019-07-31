/* eslint-disable no-unneeded-ternary */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/button-has-type */
/* eslint-disable react/prop-types */

import React from 'react';
import classnames from 'classnames';
import styles from './button.scss';

export default ({ buttonClass, buttonTitle, disabled, buttonType }) => (
  <button
    className={classnames(
      styles.btn,
      styles['btn-block'],
      styles[`btn-${buttonClass}`],
    )}
    disabled={disabled}
    type={buttonType ? buttonType : 'submit'}
  >
    {buttonTitle}
  </button>
);
