import * as React from 'react';

import { Skeleton } from '@material-ui/lab';

const LoadingSkeleton = (): JSX.Element => {
  return (
    <>
      <Skeleton width={125} height={20} style={{ transform: 'none' }} />
      <Skeleton height={100} style={{ transform: 'none' }} />
      <Skeleton height={100} style={{ transform: 'none' }} />
    </>
  );
};

export default LoadingSkeleton;
