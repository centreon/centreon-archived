import React from "react";
import PropTypes from "prop-types";
import classnames from 'classnames';
import styles from '../../styles/partials/form/_form.scss';
import fieldHoc from "./hoc";
import { prepareInputProps } from "./utils";

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
    <div className={classnames(styles["form-group"], {[styles["has-danger"]]: !!error})}>
      <label>
        <span>{label}</span>
        <span className={classnames(styles["label-option"], styles["required"])}>
          {topRightLabel ? topRightLabel : null}
        </span>
      </label>
      <input
        type={type}
        placeholder={placeholder}
        className={classnames(styles["form-control"],  {[styles["is-invalid"]]: !!error})}
        {...prepareInputProps(rest)}
      />
      {error ? <div className={styles["invalid-feedback"]}>{error} </div> : null}
    </div>
  );
};

InputField.displayName = "InputField";
InputField.defaultProps = {
  className: styles["form-control"],
  modifiers: [],
  renderMeta: null
};
InputField.propTypes = {
  error: PropTypes.element
};

export { InputField };

export default fieldHoc(InputField);
