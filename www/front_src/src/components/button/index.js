import React from 'react';

export default ({buttonClass, buttonTitle, disabled, buttonType}) => (
  <button
    class={'btn btn-block btn-' + buttonClass}
    disabled={disabled}
    type={buttonType ? buttonType : 'submit'}
  >
    {buttonTitle}
  </button>
);
