import React from 'react';

import {prepareInputProps} from './utils';
import fieldHoc from './hoc';

const RadioField = ({checked, error, label, info, className, ...rest}) => (
  <div class="custom-control custom-radio form-group">
    <input
      class="form-check-input"
      type="radio"
      aria-checked={checked}
      info
      {...prepareInputProps(rest)}
    />

    <label htmlFor={rest.id} class="custom-control-label">
      {label}
      {info}
    </label>

    {error ? (
      <div class="invalid-feedback">
        <i class="fas fa-exclamation-triangle" />
        <div class="field__msg  field__msg--error">{error}</div>{' '}
      </div>
    ) : null}
  </div>
);

RadioField.displayName = 'RadioField';
RadioField.defaultProps = {className: 'field'};

export {RadioField};

export default fieldHoc(RadioField);
