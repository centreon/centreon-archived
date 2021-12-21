import * as React from 'react';

import { useTheme, alpha, Skeleton } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

const useStyles = makeStyles((theme) => ({
  skeleton: {
    backgroundColor: alpha(theme.palette.grey[50], 0.4),
    margin: theme.spacing(0.5, 2, 1, 2),
  },
}));

interface Props {
  width?: number;
}

const MenuLoader = ({ width = 15 }: Props): JSX.Element => {
  const theme = useTheme();
  const classes = useStyles();

  return (
    <Skeleton
      animation="wave"
      className={classes.skeleton}
      height={theme.spacing(5)}
      width={theme.spacing(width)}
    />
  );
};

export default MenuLoader;
