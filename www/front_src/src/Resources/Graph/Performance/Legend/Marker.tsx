import * as React from 'react';

import clsx from 'clsx';

import { makeStyles } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  icon: {
    width: 9,
    height: 9,
    borderRadius: '50%',
    marginRight: theme.spacing(1),
  },
  disabled: {
    color: theme.palette.text.disabled,
  },
}));

interface Props {
  disabled?: boolean;
  color: string;
}

const LegendMarker = ({ disabled, color }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div
      className={clsx(classes.icon, { [classes.disabled]: disabled })}
      style={{ backgroundColor: color }}
    />
  );
};

export default LegendMarker;
