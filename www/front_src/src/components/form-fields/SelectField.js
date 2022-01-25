/* eslint-disable react/default-props-match-prop-types */
/* eslint-disable react/require-default-props */
/* eslint-disable react/forbid-prop-types */
/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable jsx-a11y/label-has-associated-control */
/* eslint-disable react/prop-types */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable import/no-extraneous-dependencies */

import React from 'react';

import PropTypes from 'prop-types';
import classnames from 'classnames';

import { Typography } from '@mui/material';

import styles from '../../styles/partials/form/_form.scss';

import fieldHoc from './hoc';
import { prepareInputProps } from './utils';

const renderOption = (option, key) => {
  if (typeof option === 'string') {
    return (
      <option key={key} value={option}>
        {option}
      </option>
    );
  }

  if (option.subOptions) {
    const { subOptions, text, value, ...restProps } = option;

    return (
      <optgroup
        key={`optgroup-${key}`}
        label={text}
        value={value}
        {...restProps}
      >
        {subOptions.map((o, j) => renderOption(o, `${key}-${j}`))}
      </optgroup>
    );
  }

  const { text, value, ...restProps } = option;

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
  const [defaultKey, defaultVal, isDefaultDisabled] = defaultOption || [
    null,
    '',
    true,
  ];

  return (
    <div
      className={classnames(styles['form-group'], styles.select)}
      style={styleOverride}
    >
      {label ? (
        <label>
          <Typography>{label}</Typography>
          <span className={classnames(styles['label-option'], styles.optional)}>
            {topRightLabel || null}
          </span>
        </label>
      ) : null}

      <select
        className={classnames(styles['form-control'], styles['custom-select'])}
        {...prepareInputProps(rest)}
      >
        {defaultOption !== false ? (
          <option disabled={isDefaultDisabled} value={defaultKey}>
            {defaultVal}
          </option>
        ) : null}
        {options.map(renderOption)}
      </select>

      {error && topRightLabel === 'Required' ? (
        <div className={styles['invalid-feedback']}>{error}</div>
      ) : null}
    </div>
  );
};

SelectField.displayName = 'SelectField';
SelectField.propTypes = {
  defaultOption: PropTypes.oneOfType([PropTypes.array, PropTypes.bool]),
  error: PropTypes.element,
  options: PropTypes.array,
};
SelectField.defaultProps = {
  className: styles.field,
  defaultOption: false,
  modifiers: [],
  options: [],
  styleOverride: {},
};

export { SelectField };

export default fieldHoc(SelectField);
