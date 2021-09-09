/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import clsx from 'clsx';

import { makeStyles } from '@material-ui/core';

import styles from './content-horizontal-line.scss';

const useStyles = makeStyles((theme) => ({
  background: {
    backgroundColor: theme.palette.background.default,
  },
}));

const HorizontalLineContent = ({ hrTitle, hrColor, hrTitleColor }) => {
  const classes = useStyles();

  return (
    <div
      className={clsx(styles['content-hr'], {
        [styles[`content-hr-${hrColor}`]]: hrColor,
      })}
    >
      <span
        className={clsx(styles['content-hr-title'], classes.background, {
          [styles[`content-hr-title-${hrTitleColor}`]]: hrTitleColor,
        })}
      >
        {hrTitle}
      </span>
    </div>
  );
};

export default HorizontalLineContent;
