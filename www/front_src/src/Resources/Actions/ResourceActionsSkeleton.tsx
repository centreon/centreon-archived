import * as React from 'react';

import { makeStyles } from '@mui/styles';

import { LoadingSkeleton } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  resourceActions: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: `${theme.spacing(18)} ${theme.spacing(
      17,
    )} ${theme.spacing(11)} min-content`,
    gridTemplateRows: theme.spacing(3.5),
  },
}));

const ResourceActionsSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.resourceActions}>
      <LoadingSkeleton height="100%" />
      <LoadingSkeleton height="100%" />
      <LoadingSkeleton height="100%" />
      <LoadingSkeleton height={24} variant="circular" width={24} />
    </div>
  );
};

export default ResourceActionsSkeleton;
