/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React, { ReactNode } from 'react';
import classnames from 'classnames';
import styles from '../../styles/partials/form/_form.scss';
import { prepareInputProps } from './utils';
import fieldHoc from './hoc';

export interface RadioProps {
  checked: boolean;
  error: ReactNode | null;
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
}: Props) => (
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
      checked={checked}
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
