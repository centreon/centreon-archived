import * as React from 'react';

import clsx from 'clsx';

import { makeStyles, Theme } from '@material-ui/core';

const useStyles = makeStyles<Theme, { color?: string }>((theme) => ({
  icon: {
    width: 9,
    height: 9,
    borderRadius: '50%',
    marginRight: theme.spacing(1),
    backgroundColor: ({ color }) => color,
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
  const classes = useStyles({ color });

  return (
    <div className={clsx(classes.icon, { [classes.disabled]: disabled })} />
  );
};

export default LegendMarker;
