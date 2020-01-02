/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';
import classnames from 'classnames';
import styles from '../../styles/partials/form/_form.scss';

interface Props {
  className: string;
  children: any; // to be remplaced by ReactNode when types definition will be included
  isError: boolean;
  tagName: any; // to be remplaced by ReactNode when types definition will be included
  restProps: object;
}

const FieldMsg = ({
  className,
  children,
  isError,
  tagName: FieldMsgTagName,
  ...restProps
}: Props) => (
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
