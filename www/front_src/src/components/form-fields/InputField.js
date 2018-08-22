import React from 'react';
/* @jsx h */
import PropTypes from 'prop-types';
import fieldHoc from './hoc';
import {prepareInputProps} from './utils';

const InputField = ({
  type,
  label,
  placeholder,
  error,
  topRightLabel,
  modifiers,
  renderMeta,
  ...rest
}) => {
  return (
    <div class={'form-group' + (error ? ' has-danger' : '')}>
      <label>
        <span>{label}</span>
        <span class="label-option required">{topRightLabel ? topRightLabel : null}</span>
      </label>
      <input
        type={type}
        placeholder={placeholder}
        class={'form-control' + (error ? ' is-invalid' : '')}
        {...prepareInputProps(rest)}
      />
      {error ? (
        <div class="invalid-feedback">
          {error}{' '}
        </div>
      ) : null}
    </div>
  );
};

InputField.displayName = 'InputField';
InputField.defaultProps = {
  className: 'form-control',
  modifiers: [],
  renderMeta: null,
};
InputField.propTypes = {
  error: PropTypes.element,
};

export {InputField};

export default fieldHoc(InputField);
