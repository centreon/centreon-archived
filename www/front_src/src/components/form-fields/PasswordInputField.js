/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable react/require-default-props */
/* eslint-disable react/default-props-match-prop-types */
/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable jsx-a11y/label-has-associated-control */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import styles from '../../styles/partials/form/_form.scss';
import fieldHoc from './hoc';
import { prepareInputProps } from './utils';

class PasswordInputField extends Component {
  state = {
    shown: false,
  };

  toggleShowPassword = () => {
    const { shown } = this.state;
    this.setState({
      shown: !shown,
    });
  };

  render() {
    const {
      label,
      placeholder,
      error,
      topRightLabel,
      modifiers,
      renderMeta,
      forgotPasswordRoute,
      forgotPasswordLink,
      ...rest
    } = this.props;

    const { shown } = this.state;

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
        <div className={styles['input-group']}>
          <input
            type={shown ? 'text' : 'password'}
            placeholder={placeholder}
            className={classnames(styles['form-control'], styles.password, {
              [styles['is-invalid']]: !!error,
            })}
            {...prepareInputProps(rest)}
          />
        </div>
        {error ? (
          <div className={styles['invalid-feedback']}>{error}</div>
        ) : null}
      </div>
    );
  }
}

PasswordInputField.displayName = 'PasswordInputField';
PasswordInputField.defaultProps = {
  className: styles['form-control'],
  modifiers: [],
  renderMeta: null,
};
PasswordInputField.propTypes = {
  error: PropTypes.element,
};

export { PasswordInputField };

export default fieldHoc(PasswordInputField);
