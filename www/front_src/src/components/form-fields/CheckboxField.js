/* eslint-disable react/forbid-prop-types */
/* eslint-disable react/require-default-props */
/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable import/no-extraneous-dependencies */

import React from 'react';

import PropTypes from 'prop-types';
import classnames from 'classnames';
import { FormControlLabel, Checkbox, Typography } from '@mui/material';

import styles from '../../styles/partials/form/_form.scss';

import fieldHoc from './hoc';

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
      <FormControlLabel
        aria-checked={checked}
        checked={value}
        control={<Checkbox color="primary" size="small" />}
        defaultChecked={value === trueValue}
        label={label}
        name={rest.name}
        onChange={
          onChange && callbackWithValue(trueValue, falseValue, onChange)
        }
      />
    </div>
    {error ? (
      <Typography color="error" variant="body2">
        {error}
      </Typography>
    ) : null}
  </div>
);

CheckboxField.displayName = 'CheckboxField';
CheckboxField.propTypes = {
  className: PropTypes.string,
  error: PropTypes.element,
  falseValue: PropTypes.any,
  id: PropTypes.string.isRequired,
  label: PropTypes.string,
  trueValue: PropTypes.any,
  value: PropTypes.bool,
};
CheckboxField.defaultProps = {
  className: styles.field,
  falseValue: false,
  trueValue: true,
};

export { CheckboxField };

export default fieldHoc(CheckboxField);
