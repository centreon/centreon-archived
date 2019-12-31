/* eslint-disable jsx-a11y/label-has-associated-control */
/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable react/require-default-props */
/* eslint-disable react/default-props-match-prop-types */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable import/no-extraneous-dependencies */

import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import styles from '../../styles/partials/form/_form.scss';
import fieldHoc from './hoc';
import { prepareInputProps } from './utils';

interface Props {
  type: string;
  label: string;
  placeholder: string;
  error: ReactNode | null;
  topRightLabel: string;
  modifiers: Array;
  rest: object;
}

const InputField = ({
  type,
  label,
  placeholder,
  error,
  topRightLabel,
  modifiers,
  ...rest
}: Props) => {
  return (
    <div
      className={classnames(styles['form-group'], {
        [styles['has-danger']]: !!error,
      })}
    >
      <label>
        <span>{label}</span>
        <span className={classnames(styles['label-option'], styles.required)}>
          {topRightLabel || null}
        </span>
      </label>
      <input
        type={type}
        placeholder={placeholder}
        className={classnames(styles['form-control'], {
          [styles['is-invalid']]: !!error,
        })}
        {...prepareInputProps(rest)}
      />
      {error ? <div className={styles['invalid-feedback']}>{error}</div> : null}
    </div>
  );
};

InputField.displayName = 'InputField';
InputField.defaultProps = {
  className: styles['form-control'],
  modifiers: [],
};
InputField.propTypes = {
  error: PropTypes.element,
};

export { InputField };

export default fieldHoc(InputField);
