import * as React from 'react';

import { makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

interface Props {
  displayTitleSkeleton: boolean;
  graphHeight: number;
}

const useSkeletonStyles = makeStyles((theme) => ({
  loadingSkeleton: {
    display: 'grid',
    gridGap: theme.spacing(1),
    gridTemplateRows: ({ graphHeight, displayTitleSkeleton }: Props): string =>
      `${displayTitleSkeleton ? '1fr' : ''} ${graphHeight}px ${theme.spacing(
        7,
      )}px`,
    height: '100%',
  },
  loadingSkeletonLine: {
    paddingBottom: theme.spacing(1),
    transform: 'none',
  },
}));

const LoadingSkeleton = ({
  graphHeight,
  displayTitleSkeleton,
}: Props): JSX.Element => {
  const classes = useSkeletonStyles({ displayTitleSkeleton, graphHeight });

  const skeletonLine = <Skeleton className={classes.loadingSkeletonLine} />;

  return (
    <div className={classes.loadingSkeleton}>
      {displayTitleSkeleton && skeletonLine}
      {skeletonLine}
      {skeletonLine}
    </div>
  );
};

export default LoadingSkeleton;
