import * as React from 'react';

import { makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

interface Props {
  graphHeight: number;
  displayTitleSkeleton: boolean;
}

const useSkeletonStyles = makeStyles((theme) => ({
  loadingSkeleton: {
    display: 'grid',
    gridTemplateRows: ({ graphHeight, displayTitleSkeleton }: Props): string =>
      `${displayTitleSkeleton ? '1fr' : ''} ${graphHeight}px 2fr`,
    gridGap: theme.spacing(1),
    height: '100%',
  },
  loadingSkeletonLine: {
    transform: 'none',
    paddingBottom: theme.spacing(1),
  },
}));

const LoadingSkeleton = ({
  graphHeight,
  displayTitleSkeleton,
}: Props): JSX.Element => {
  const classes = useSkeletonStyles({ graphHeight, displayTitleSkeleton });

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
