/* eslint-disable react/forbid-prop-types */
/* eslint-disable react/require-default-props */
/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable import/no-extraneous-dependencies */

import React from 'react';

import PropTypes from 'prop-types';
import classnames from 'classnames';

import styles from '../../styles/partials/form/_form.scss';

import fieldHoc from './hoc';
import { prepareInputProps } from './utils';

const callbackWithValue = (trueValue, falseValue, callback) => (e) =>
  callback(e.target.checked ? trueValue : falseValue);

const CheckboxField = ({
  checked,
  className,
  error,
  falseValue,
  fieldMsg,
  label,
  onBlur,
  onChange,
  trueValue,
  value,
  info,
  ...rest
}) => (
  <div
    className={classnames(styles['form-group'], {
      [styles['has-danger']]: !!error,
    })}
  >
    <div
      className={classnames(
        styles['custom-control'],
        styles['custom-checkbox orange'],
      )}
    >
      <input
        {...prepareInputProps(rest)}
        aria-checked={checked}
        checked={value}
        defaultChecked={value === trueValue}
        onChange={
          onChange && callbackWithValue(trueValue, falseValue, onChange)
        }
        focusin={onBlur && callbackWithValue(trueValue, falseValue, onBlur)}
        className={styles['custom-control-input']}
        type="checkbox"
      />
      <label htmlFor={rest.id} className={styles['custom-control-label']}>
        {label}
        {info}
      </label>
    </div>
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

CheckboxField.displayName = 'CheckboxField';
CheckboxField.propTypes = {
  id: PropTypes.string.isRequired,
  label: PropTypes.string,
  className: PropTypes.string,
  value: PropTypes.bool,
  trueValue: PropTypes.any,
  falseValue: PropTypes.any,
  error: PropTypes.element,
};
CheckboxField.defaultProps = {
  className: styles.field,
  trueValue: true,
  falseValue: false,
};

export { CheckboxField };

export default fieldHoc(CheckboxField);
