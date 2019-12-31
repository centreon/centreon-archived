/* eslint-disable react/require-default-props */
/* eslint-disable react/forbid-prop-types */
/* eslint-disable react/prop-types */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable class-methods-use-this */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable no-return-assign */
/* eslint-disable no-plusplus */
/* eslint-disable import/no-extraneous-dependencies */

import React, { Component, ReactNode } from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import styles from '../../styles/partials/form/_form.scss';
import getErrorMsg, { ErrorMessage } from './getErrorMsg';
import FieldMsg from './FieldMsg';

interface Input {
  name: string;
  onFocus: Function;
  onBlur: Function;
  value: string | number | boolean;
}

export interface Meta {
  touched: boolean;
  error: ErrorMessage;
}

interface Props {
  input: Input;
  meta: Meta;
  label: string;
  autoComplete: string;
  rest: object;
}

interface State {
  isFocused: boolean;
}

let fid = 0;

const nextId = () => ++fid;

const fieldHoc = (WrapComponent: ReactNode) => {
  class FieldHoc extends Component<Props, State> {
    public constructor(props) {
      super(props);

      this.state = { isFocused: false };

      ['getId', 'handleFocus', 'handleBlur', 'isInputValue', 'renderError'].map(
        (fName) => (this[fName] = this[fName].bind(this)),
      );
    }

    private getId() {
      const { name } = this.props.input;

      if (!this.fieldId) {
        this.fieldId = nextId();
      }

      return `field-${name}-${this.fieldId}`;
    }

    private handleFocus(e: MouseEvent) {
      const {
        input: { onFocus },
      } = this.props;

      this.setState({ isFocused: true });

      if (onFocus) onFocus(e);
    }

    private handleBlur() {
      const {
        input: { onBlur, value },
      } = this.props;

      this.setState({ isFocused: false });

      if (onBlur) {
        return onBlur(value);
      }
    }

    private isInputValue(value: string | null | undefined) {
      return value !== undefined && value !== null && value !== '';
    }

    private renderError() {
      const {
        meta: { touched, error },
      } = this.props;

      return touched && error ? (
        <FieldMsg>{getErrorMsg(error)}</FieldMsg>
      ) : null;
    }

    public render() {
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
          label={label}
          onFocus={this.handleFocus}
          onBlur={this.handleBlur}
          id={this.getId()}
          error={this.renderError()}
        />
      );
    }
  }

  FieldHoc.displayName = `FieldHoc(${WrapComponent.displayName})`;

  FieldHoc.propTypes = {
    meta: PropTypes.object.isRequired,
    input: PropTypes.object.isRequired,
    label: PropTypes.string,
    onFocus: PropTypes.func,
    onBlur: PropTypes.func,
  };

  return FieldHoc;
};

export default fieldHoc;
