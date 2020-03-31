import * as React from 'react';

import { makeStyles, Avatar, fade, Theme } from '@material-ui/core';
import { CreateCSSProperties } from '@material-ui/core/styles/withStyles';

const useStyles = makeStyles<Theme, { color?: string }>((theme) => ({
  chip: ({ color }): CreateCSSProperties => ({
    width: theme.spacing(4),
    height: theme.spacing(4),
    ...(color && {
      backgroundColor: fade(color, 0.1),
      color,
    }),
  }),
}));

interface Props {
  icon: JSX.Element;
  color?: string;
}

const Chip = ({ icon, color }: Props): JSX.Element => {
  const classes = useStyles({ color });

  return <Avatar className={`${classes.chip}`}>{icon}</Avatar>;
};

export default Chip;
