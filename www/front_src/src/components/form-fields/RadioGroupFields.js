/* eslint-disable react/default-props-match-prop-types */
/* eslint-disable react/forbid-prop-types */
/* eslint-disable react/prop-types */
/* eslint-disable react/no-array-index-key */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable import/no-named-as-default */
/* eslint-disable import/no-extraneous-dependencies */

import PropTypes from 'prop-types';
import { useTranslation } from 'react-i18next';

import { Radio, FormControlLabel, Typography } from '@mui/material';

import styles from '../../styles/partials/form/_form.scss';

const getValue = (item) => (item.value ? item.value : item);

const getLabel = (item) => (item.label ? item.label : item);

const RadioGroupField = ({ options, className, label, meta, ...rest }) => {
  const { t } = useTranslation();
  const { error, touched, ...restMeta } = meta;

  const renderOptions = (props) =>
    options.map((item, i) => {
      return (
        <FormControlLabel
          checked={getValue(item).toString() === props.input.value}
          control={<Radio color="primary" size="small" />}
          key={i}
          label={t(getLabel(item))}
          labelPlacement="start"
          value={getValue(item)}
          onChange={props.input.onChange}
        />
      );
    });

  return (
    <div className={styles['form-group']}>
      {renderOptions({ ...rest, meta: { ...restMeta } })}
      {touched && error ? (
        <Typography style={{ color: '#d0021b' }} variant="body2">
          {error}
        </Typography>
      ) : null}
    </div>
  );
};

RadioGroupField.displayName = 'RadioGroupField';
RadioGroupField.propTypes = {
  options: PropTypes.array.isRequired,
};

RadioGroupField.defaultProps = { className: styles['radio-group-field'] };

export default RadioGroupField;
