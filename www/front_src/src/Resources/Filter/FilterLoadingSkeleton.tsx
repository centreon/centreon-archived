import * as React from 'react';

import { Skeleton } from '@material-ui/lab';

const FilterLoadingSkeleton = (): JSX.Element => {
  return <Skeleton height={36} style={{ transform: 'none' }} width={200} />;
};

export default FilterLoadingSkeleton;
