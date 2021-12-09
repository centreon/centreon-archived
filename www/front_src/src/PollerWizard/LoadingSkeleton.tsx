import * as React from 'react';

import { Skeleton } from '@material-ui/lab';
import { makeStyles } from '@material-ui/core';

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
      <Skeleton className={classes.skeleton} variant="rect" />
    </div>
  );
};

export default LoadingSkeleton;
