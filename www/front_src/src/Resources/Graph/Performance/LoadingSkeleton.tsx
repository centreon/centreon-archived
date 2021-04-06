import * as React from 'react';

import { makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

interface Props {
  graphHeight: number;
  displayTitle: boolean;
}

const useSkeletonStyles = makeStyles((theme) => ({
  loadingSkeleton: {
    display: 'grid',
    gridTemplateRows: ({ graphHeight, displayTitle }: Props): string =>
      `${displayTitle ? '1fr' : ''} ${graphHeight}px 2fr`,
    gridGap: theme.spacing(1),
    height: '100%',
  },
  loadingSkeletonLine: {
    transform: 'none',
    paddingBottom: theme.spacing(1),
  },
}));

const LoadingSkeleton = ({ graphHeight, displayTitle }: Props): JSX.Element => {
  const classes = useSkeletonStyles({ graphHeight, displayTitle });

  const skeletonLine = <Skeleton className={classes.loadingSkeletonLine} />;

  return (
    <div className={classes.loadingSkeleton}>
      {displayTitle && skeletonLine}
      {skeletonLine}
      {skeletonLine}
    </div>
  );
};

export default LoadingSkeleton;
