import React from 'react';
import PropTypes from 'prop-types';
import fieldHoc from './hoc';
import {prepareInputProps} from './utils';

const renderOption = (option, key) => {
  if (typeof option === 'string') {
    return (
      <option key={key} value={option}>
        {option}
      </option>
    );
  }

  if (option.subOptions) {
    const {subOptions, text, value, ...restProps} = option;

    return (
      <optgroup key={`optgroup-${key}`} label={text} value={value} {...restProps}>
        {subOptions.map((o, j) => renderOption(o, `${key}-${j}`))}
      </optgroup>
    );
  }

  const {text, value, ...restProps} = option;

  return (
    <option key={key} value={value} {...restProps}>
      {text}
    </option>
  );
};

const SelectField = ({
  className,
  defaultOption,
  error,
  label,
  modifiers,
  options,
  styleOverride,
  topRightLabel,
  ...rest
}) => {
  const [defaultKey, defaultVal, isDefaultDisabled] = defaultOption || [null, '', true];
  const modClassName = modifiers
    .map(m => `field--${m}`)
    .concat(className)
    .join(' ');

  return (
    <div className="form-group select" style={styleOverride}>
      {label ? (
        <label>
          <span> {label} </span>
          <span class="label-option optional"> {topRightLabel ? topRightLabel : null} </span>
        </label>
      ) : null}

      <select className="form-control custom-select" {...prepareInputProps(rest)}>
        {defaultOption !== false ? (
          <option value={defaultKey} disabled={isDefaultDisabled}>
            {defaultVal}
          </option>
        ) : null}
        {options.map(renderOption)}
      </select>

      {error && topRightLabel === 'Required' ? (
        <div class="invalid-feedback">
          {error}
        </div>
      ) : null}
    </div>
  );
};

SelectField.displayName = 'SelectField';
SelectField.PropTypes = {
  options: PropTypes.array,
  defaultOption: PropTypes.oneOfType([PropTypes.array, PropTypes.bool]),
  error: PropTypes.element,
};
SelectField.defaultProps = {
  className: 'field',
  styleOverride: {},
  defaultOption: false,
  modifiers: [],
  options: [],
};

export {SelectField};

export default fieldHoc(SelectField);
