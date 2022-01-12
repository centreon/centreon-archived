import * as React from 'react';

import { Skeleton } from '@mui/material';

const FilterLoadingSkeleton = (): JSX.Element => {
  return <Skeleton height={33} style={{ transform: 'none' }} width={175} />;
};

export default FilterLoadingSkeleton;
