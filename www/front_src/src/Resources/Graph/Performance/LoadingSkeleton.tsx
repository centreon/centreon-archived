import * as React from 'react';

import { makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

const useSkeletonStyles = makeStyles((theme) => ({
  loadingSkeleton: {
    display: 'grid',
    gridTemplateRows: '1fr 10fr 2fr',
    gridGap: theme.spacing(1),
    height: '100%',
  },
  loadingSkeletonLine: {
    transform: 'none',
    paddingBottom: theme.spacing(1),
  },
}));

const LoadingSkeleton = (): JSX.Element => {
  const classes = useSkeletonStyles();

  const skeletonLine = <Skeleton className={classes.loadingSkeletonLine} />;

  return (
    <div className={classes.loadingSkeleton}>
      {skeletonLine}
      {skeletonLine}
      {skeletonLine}
    </div>
  );
};

export default LoadingSkeleton;
