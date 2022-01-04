import * as React from 'react';

import { Skeleton } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

const useStyles = makeStyles((theme) => ({
  skeleton: {
    height: theme.spacing(5),
    width: '100%',
  },
  skeletonContainer: {
    columnGap: theme.spacing(3),
    display: 'flex',
    flexDirection: 'column',
    width: '100%',
  },
}));

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.skeletonContainer}>
      <Skeleton className={classes.skeleton} variant="rectangular" />
    </div>
  );
};

export default LoadingSkeleton;
