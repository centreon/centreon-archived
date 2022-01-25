import * as React from 'react';

import { Skeleton } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

const useStyles = makeStyles((theme) => ({
  skeleton: {
    height: 62,
    transform: 'none',
    width: '100%',
  },
  skeletons: {
    display: 'grid',
    gridGap: theme.spacing(1),
  },
}));

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  const serviceLoadingSkeleton = <Skeleton className={classes.skeleton} />;

  return (
    <div className={classes.skeletons}>
      {serviceLoadingSkeleton}
      {serviceLoadingSkeleton}
      {serviceLoadingSkeleton}
    </div>
  );
};

export default LoadingSkeleton;
