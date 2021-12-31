import * as React from 'react';

import { makeStyles } from '@material-ui/core';

import { LoadingSkeleton } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  resourceActions: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: `${theme.spacing(18)}px ${theme.spacing(
      17,
    )}px ${theme.spacing(11)}px min-content`,
    gridTemplateRows: `${theme.spacing(3.5)}px`,
  },
}));

const ResourceActionsSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.resourceActions}>
      <LoadingSkeleton height="100%" />
      <LoadingSkeleton height="100%" />
      <LoadingSkeleton height="100%" />
      <LoadingSkeleton height={24} variant="circle" width={24} />
    </div>
  );
};

export default ResourceActionsSkeleton;
