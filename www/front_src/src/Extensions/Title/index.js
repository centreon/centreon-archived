/* eslint-disable react/jsx-filename-extension */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/prop-types */

import React from 'react';

import clsx from 'clsx';

import styles from './custom-title.scss';

const Title = ({
  icon,
  label,
  title,
  titleColor,
  customTitleStyles,
  onClick,
  style,
  labelStyle,
  children,
}) => (
  <div
    className={clsx(
      styles['custom-title'],
      customTitleStyles ? styles['custom-title-styles'] : '',
    )}
    style={style}
    onClick={onClick}
  >
    {icon ? (
      <span
        className={clsx(styles['custom-title-icon'], {
          [styles[`custom-title-icon-${icon}`]]: true,
        })}
      />
    ) : null}
    <div className={styles['custom-title-label-container']}>
      <span
        className={clsx(styles['custom-title-label'], styles[titleColor || ''])}
        style={labelStyle}
        title={title || label}
      >
        {label}
      </span>
      {children}
    </div>
  </div>
);

export default Title;
