/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable react/prop-types */
/* eslint-disable react/jsx-filename-extension */

import React from 'react';
import classnames from 'classnames';
import styles from '../../styles/partials/form/_form.scss';
import { prepareInputProps } from './utils.ts';
import fieldHoc from './hoc.tsx';

interface RadioProps {
  checked: boolean;
  error: any; // to be remplaced by ReactNode when types definition will be included
  label: string;
  info: string;
  className: string;
  rest: object;
}

const RadioField = ({
  checked,
  error,
  label,
  info,
  className,
  ...rest
}: RadioProps) => (
  <div
    className={classnames(
      styles['custom-control'],
      styles['custom-radio'],
      styles['form-group'],
    )}
  >
    <input
      className={styles['form-check-input']}
      type="radio"
      aria-checked={checked}
      info
      {...prepareInputProps(rest)}
    />

    <label htmlFor={rest.id} className={styles['custom-control-label']}>
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
