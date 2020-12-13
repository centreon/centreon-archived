import * as React from 'react';

import { Skeleton } from '@material-ui/lab';

import { useStyles } from '.';

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  const serviceLoadingSkeleton = (
    <div className={classes.serviceDetails}>
      <Skeleton variant="circle" width={25} height={25} />
      <Skeleton height={25} />
      <Skeleton width={50} height={25} />
    </div>
  );

  return (
    <div className={classes.services}>
      {serviceLoadingSkeleton}
      {serviceLoadingSkeleton}
      {serviceLoadingSkeleton}
    </div>
  );
};

export default LoadingSkeleton;
