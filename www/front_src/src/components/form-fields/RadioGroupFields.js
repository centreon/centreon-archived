import React from 'react';
import PropTypes from 'prop-types';

import FieldMsg from './FieldMsg';
import RadioField from './RadioField';

const getValue = item => (item.value ? item.value : item);

const getLabel = item => (item.label ? item.label : item);

const getInfo = item => (item.info ? item.info : null);

const renderOptions = (options, rest) =>
  options.map((item, i) => (
    <RadioField
      key={i}
      {...rest}
      value={getValue(item)}
      label={getLabel(item)}
      info={getInfo(item)}
      checked={getValue(item) === rest.input.value}
      className="radio-group-field__radio"
    />
  ));

const RadioGroupField = ({options, className, label, meta, ...rest}) => {
  const {error, touched, ...restMeta} = meta;

  return (
    <div className="form-group">
      {renderOptions(options, {...rest, meta: {...restMeta}})}
      {touched && error ? <FieldMsg>{error}</FieldMsg> : null}
    </div>
  );
};

RadioGroupField.displayName = 'RadioGroupField';
RadioGroupField.propTypes = {
  options: PropTypes.array.isRequired,
};

RadioGroupField.defaultProps = {className: 'radio-group-field'};

export default RadioGroupField;
