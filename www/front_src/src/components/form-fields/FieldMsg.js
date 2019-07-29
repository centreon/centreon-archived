/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';
import classnames from 'classnames';
import styles from '../../styles/partials/form/_form.scss';

const FieldMsg = ({
  className,
  children,
  isError,
  tagName: FieldMsgTagName,
  ...restProps
}) => (
  <FieldMsgTagName
    className={classnames(styles.field__msg, styles[className], {
      [styles['field__msg--error']]: !!isError,
    })}
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
