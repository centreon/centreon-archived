/* eslint-disable react/default-props-match-prop-types */
/* eslint-disable react/forbid-prop-types */
/* eslint-disable react/prop-types */
/* eslint-disable react/no-array-index-key */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable import/no-named-as-default */
/* eslint-disable import/no-extraneous-dependencies */

import React from 'react';

import PropTypes from 'prop-types';
import { useTranslation } from 'react-i18next';

import styles from '../../styles/partials/form/_form.scss';

import FieldMsg from './FieldMsg';
import RadioField from './RadioField';

const getValue = (item) => (item.value ? item.value : item);

const getLabel = (item) => (item.label ? item.label : item);

const getInfo = (item) => (item.info ? item.info : null);

const RadioGroupField = ({ options, className, label, meta, ...rest }) => {
  const { t } = useTranslation();
  const { error, touched, ...restMeta } = meta;

  const renderOptions = (props) =>
    options.map((item, i) => (
      <RadioField
        key={i}
        {...props}
        checked={getValue(item) === props.input.value}
        className={styles['radio-group-field__radio']}
        info={getInfo(item)}
        label={t(getLabel(item))}
        value={getValue(item)}
      />
    ));

  return (
    <div className={styles['form-group']}>
      {renderOptions({ ...rest, meta: { ...restMeta } })}
      {touched && error ? <FieldMsg>{error}</FieldMsg> : null}
    </div>
  );
};

RadioGroupField.displayName = 'RadioGroupField';
RadioGroupField.propTypes = {
  options: PropTypes.array.isRequired,
};

RadioGroupField.defaultProps = { className: styles['radio-group-field'] };

export default RadioGroupField;
