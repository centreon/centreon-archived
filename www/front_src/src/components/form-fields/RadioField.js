/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable react/prop-types */
/* eslint-disable react/jsx-filename-extension */

import React from 'react';

import classnames from 'classnames';
import { Typography } from '@mui/material';

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
      className={styles['form-check-input']}
      type="radio"
      {...prepareInputProps(rest)}
    />

    <label className={styles['custom-control-label']} htmlFor={rest.id}>
      {label}
      {info}
    </label>

    {error ? (
      <Typography style={{ color: '#d0021b' }} variant="body2">
        {error}
      </Typography>
    ) : null}
  </div>
);

RadioField.displayName = 'RadioField';
RadioField.defaultProps = { className: styles.field };

export { RadioField };

export default fieldHoc(RadioField);
