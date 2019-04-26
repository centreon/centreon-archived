import React from "react";
import classnames from 'classnames';
import styles from './button.scss';

export default ({ buttonClass, buttonTitle, disabled, buttonType }) => (
  <button
    className={classnames(styles["btn"], styles["btn-block"], styles[`btn-${buttonClass}`])}
    disabled={disabled}
    type={buttonType ? buttonType : "submit"}
  >
    {buttonTitle}
  </button>
);
