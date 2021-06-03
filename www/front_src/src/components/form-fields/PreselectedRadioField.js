/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import classnames from 'classnames';

import styles from '../../styles/partials/form/_form.scss';

import { prepareInputProps } from './utils';
import fieldHoc from './hoc';

const RadioField = ({ checked, error, label, info, className, ...rest }) => (
  <div
    className={classnames(
      styles['custom-control'],
      styles['custom-radio'],
      styles['form-group'],
    )}
  >
    <input
      info
      aria-checked={checked}
      checked={checked}
      className={styles['form-check-input']}
      type="radio"
      {...prepareInputProps(rest)}
    />

    <label className={styles['custom-control-label']} htmlFor={rest.id}>
      {label}
      {info}
    </label>

    {error ? (
      <div className={styles['invalid-feedback']}>
        <div
          className={classnames(styles.field__msg, styles['field__msg--error'])}
        >
          {error}
        </div>
      </div>
    ) : null}
  </div>
);

RadioField.displayName = 'RadioField';
RadioField.defaultProps = { className: styles.field };

export { RadioField };

export default fieldHoc(RadioField);
