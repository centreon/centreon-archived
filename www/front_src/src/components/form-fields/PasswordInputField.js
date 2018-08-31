import React, { Component } from "react";

import PropTypes from "prop-types";
import fieldHoc from "./hoc";
import { prepareInputProps } from "./utils";
import Icon from "../icon";

class PasswordInputField extends Component {
  state = {
    shown: false
  };

  toggleShowPassword = () => {
    const { shown } = this.state;
    this.setState({
      shown: !shown
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
      <div class={"form-group" + (error ? " has-danger" : "")}>
        <label>
          <span>{label}</span>
          <span class="label-option required">
            {topRightLabel ? topRightLabel : null}
          </span>
        </label>
        <div class="input-group">
          <input
            type={shown ? "text" : "password"}
            placeholder={placeholder}
            class={"form-control password" + (error ? " is-invalid" : "")}
            {...prepareInputProps(rest)}
          />
          <span class="input-group-text" onClick={this.toggleShowPassword}>
            <Icon face={shown ? "eye" : "eye-slash"} />
          </span>
        </div>
        {error ? (
          <div class="invalid-feedback">
            <Icon face="exclamation-triangle" />
            {error}{" "}
          </div>
        ) : null}
      </div>
    );
  }
}

PasswordInputField.displayName = "PasswordInputField";
PasswordInputField.defaultProps = {
  className: "form-control",
  modifiers: [],
  renderMeta: null
};
PasswordInputField.propTypes = {
  error: PropTypes.element
};

export { PasswordInputField };

export default fieldHoc(PasswordInputField);
