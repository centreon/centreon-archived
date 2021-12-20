import * as React from 'react';

import { useTheme, makeStyles, alpha } from '@material-ui/core';

import { LoadingSkeleton } from '@centreon/ui';

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
    <LoadingSkeleton
      animation="wave"
      className={classes.skeleton}
      height={theme.spacing(5)}
      variant="text"
      width={theme.spacing(width)}
    />
  );
};

export default MenuLoader;
