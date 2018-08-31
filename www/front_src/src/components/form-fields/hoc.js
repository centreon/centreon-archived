import React, { Component } from "react";
import PropTypes from "prop-types";
import className from "class-name";
import getErrorMsg from "./getErrorMsg";
import FieldMsg from "./FieldMsg";

let fid = 0;

const nextId = () => ++fid;

const fieldHoc = WrapComponent => {
  class FieldHoc extends Component {
    constructor(props) {
      super(props);

      this.state = { isFocused: false };

      ["getId", "handleFocus", "handleBlur", "isInputValue", "renderError"].map(
        fName => (this[fName] = this[fName].bind(this))
      );
    }

    getId() {
      const { name } = this.props.input;

      if (!this.fieldId) {
        this.fieldId = nextId();
      }

      return `field-${name}-${this.fieldId}`;
    }

    handleFocus(e) {
      const {
        input: { onFocus }
      } = this.props;

      this.setState({ isFocused: true });

      if (onFocus) onFocus(e);
    }

    handleBlur(e) {
      const {
        input: { onBlur }
      } = this.props;

      this.setState({ isFocused: false });

      if (onBlur) onBlur(e);
    }

    isInputValue(value) {
      return value !== undefined && value !== null && value !== "";
    }

    renderError() {
      const {
        meta: { touched, error }
      } = this.props;

      return touched && error ? (
        <FieldMsg>{getErrorMsg(error)}</FieldMsg>
      ) : null;
    }

    render() {
      const { isFocused } = this.state;
      const { input, meta, label, autoComplete, ...rest } = this.props;

      const extra =
        autoComplete === "off" ? { autoComplete: this.getId() } : {};

      return (
        <WrapComponent
          className={className({
            field: true,
            "has-danger": meta.invalid && meta.touched,
            "has-value": this.isInputValue(input.value),
            "has-focus": isFocused
          })}
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
    onBlur: PropTypes.func
  };

  return FieldHoc;
};

export default fieldHoc;
