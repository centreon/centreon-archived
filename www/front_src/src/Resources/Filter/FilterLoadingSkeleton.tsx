import * as React from 'react';

import { Skeleton } from '@material-ui/lab';

const FilterLoadingSkeleton = (): JSX.Element => {
  return <Skeleton width={200} height={36} style={{ transform: 'none' }} />;
};

export default FilterLoadingSkeleton;
