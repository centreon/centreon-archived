import React from 'react';

const FieldMsg = ({className, children, isError, tagName: FieldMsgTagName, ...restProps}) => (
  <FieldMsgTagName
    className={`field__msg ${className} ${isError ? 'field__msg--error' : ''}`}
    {...restProps}
  >
    {children}
  </FieldMsgTagName>
);

FieldMsg.defaultProps = {
  className: '',
  isError: true,
  tagName: 'div',
};

export default FieldMsg;
