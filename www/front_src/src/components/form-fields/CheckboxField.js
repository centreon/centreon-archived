import React from 'react';
import PropTypes from 'prop-types';

import fieldHoc from './hoc';
import {prepareInputProps} from './utils';

const callbackWithValue = (trueValue, falseValue, callback) => e =>
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
  <div class={'form-group' + (error ? ' has-danger' : '')}>
    <div class={'custom-control custom-checkbox orange'}>
      <input
        {...prepareInputProps(rest)}
        aria-checked={checked}
        checked={value}
        defaultChecked={value === trueValue}
        onChange={onChange && callbackWithValue(trueValue, falseValue, onChange)}
        onBlur={onBlur && callbackWithValue(trueValue, falseValue, onBlur)}
        className="custom-control-input"
        type="checkbox"
      />
      <label htmlFor={rest.id} class="custom-control-label">
        {label}
        {info}
      </label>
    </div>
    {error ? (
      <div class="invalid-feedback">
        <i class="fas fa-exclamation-triangle" />
        <div class="field__msg  field__msg--error">{error}</div>{' '}
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
  className: 'field',
  trueValue: true,
  falseValue: false,
};

export {CheckboxField};

export default fieldHoc(CheckboxField);
