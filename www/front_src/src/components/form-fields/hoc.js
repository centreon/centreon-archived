/* eslint-disable consistent-return */
/* eslint-disable react/require-default-props */
/* eslint-disable react/forbid-prop-types */
/* eslint-disable react/prop-types */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable class-methods-use-this */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable no-return-assign */
/* eslint-disable no-plusplus */
/* eslint-disable import/no-extraneous-dependencies */

import { Component } from 'react';

import PropTypes from 'prop-types';
import classnames from 'classnames';

import { Typography } from '@mui/material';

import styles from '../../styles/partials/form/_form.scss';

import getErrorMsg from './getErrorMsg';

let fid = 0;

const nextId = () => ++fid;

const fieldHoc = (WrapComponent) => {
  class FieldHoc extends Component {
    constructor(props) {
      super(props);

      this.state = { isFocused: false };

      ['getId', 'handleFocus', 'handleBlur', 'isInputValue', 'renderError'].map(
        (fName) => (this[fName] = this[fName].bind(this)),
      );
    }

    handleFocus(e) {
      const {
        input: { onFocus },
      } = this.props;

      this.setState({ isFocused: true });

      if (onFocus) onFocus(e);
    }

    handleBlur() {
      const {
        input: { onBlur, value },
      } = this.props;

      this.setState({ isFocused: false });

      if (onBlur) {
        return onBlur(value);
      }
    }

    getId() {
      const { name } = this.props.input;

      if (!this.fieldId) {
        this.fieldId = nextId();
      }

      return `field-${name}-${this.fieldId}`;
    }

    isInputValue(value) {
      return value !== undefined && value !== null && value !== '';
    }

    renderError() {
      const {
        meta: { touched, error },
      } = this.props;

      return touched && error ? (
        <Typography style={{ color: '#d0021b' }} variant="body2">
          {getErrorMsg(error)}
        </Typography>
      ) : null;
    }

    render() {
      const { isFocused } = this.state;
      const { input, meta, label, autoComplete, ...rest } = this.props;

      const extra =
        autoComplete === 'off' ? { autoComplete: this.getId() } : {};

      return (
        <WrapComponent
          className={classnames(
            styles.field,
            { [styles['has-danger']]: meta.invalid && meta.touched },
            { [styles['has-value']]: this.isInputValue(input.value) },
            { [styles['has-focus']]: isFocused },
          )}
          {...input}
          {...rest}
          {...extra}
          error={this.renderError()}
          id={this.getId()}
          label={label}
          onBlur={this.handleBlur}
          onFocus={this.handleFocus}
        />
      );
    }
  }

  FieldHoc.displayName = `FieldHoc(${WrapComponent.displayName})`;

  FieldHoc.propTypes = {
    input: PropTypes.object.isRequired,
    label: PropTypes.string,
    meta: PropTypes.object.isRequired,
    onBlur: PropTypes.func,
    onFocus: PropTypes.func,
  };

  return FieldHoc;
};

export default fieldHoc;
