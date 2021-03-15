import * as React from 'react';

import clsx from 'clsx';

import { makeStyles, Theme } from '@material-ui/core';

const useStyles = makeStyles<Theme, { color?: string; isInTooltip?: boolean }>(
  (theme) => ({
    icon: {
      width: 9,
      height: ({ isInTooltip }) => (isInTooltip ? 9 : '100%'),
      borderRadius: ({ isInTooltip }) => (isInTooltip ? '50%' : 0),
      marginRight: theme.spacing(1),
      backgroundColor: ({ color }) => color,
    },
    disabled: {
      color: theme.palette.text.disabled,
    },
  }),
);

interface Props {
  disabled?: boolean;
  color: string;
  isInTooltip?: boolean;
}

const LegendMarker = ({ disabled, color, isInTooltip }: Props): JSX.Element => {
  const classes = useStyles({ color, isInTooltip });

  return (
    <div className={clsx(classes.icon, { [classes.disabled]: disabled })} />
  );
};

export default LegendMarker;
