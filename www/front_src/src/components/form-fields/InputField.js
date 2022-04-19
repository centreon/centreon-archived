/* eslint-disable jsx-a11y/label-has-associated-control */
/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable react/require-default-props */
/* eslint-disable react/default-props-match-prop-types */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable import/no-extraneous-dependencies */

import PropTypes from 'prop-types';
import classnames from 'classnames';

import { Typography } from '@mui/material';

import styles from '../../styles/partials/form/_form.scss';

import fieldHoc from './hoc';
import { prepareInputProps } from './utils';

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
    <div
      className={classnames(styles['form-group'], {
        [styles['has-danger']]: !!error,
      })}
    >
      <label>
        <Typography variant="body1">{label}</Typography>
        <span className={classnames(styles['label-option'], styles.required)}>
          {topRightLabel || null}
        </span>
      </label>
      <input
        className={classnames(styles['form-control'], {
          [styles['is-invalid']]: !!error,
        })}
        placeholder={placeholder}
        type={type}
        {...prepareInputProps(rest)}
      />
      {error && (
        <Typography color="error" variant="body2">
          {error}
        </Typography>
      )}
    </div>
  );
};

InputField.displayName = 'InputField';
InputField.defaultProps = {
  className: styles['form-control'],
  modifiers: [],
  renderMeta: null,
};
InputField.propTypes = {
  error: PropTypes.element,
};

export { InputField };

export default fieldHoc(InputField);
