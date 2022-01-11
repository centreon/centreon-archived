import * as React from 'react';

import { makeStyles } from '@mui/styles';

import { LoadingSkeleton } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  globalActions: {
    alignItems: 'center',
    columnGap: theme.spacing(2),
    display: 'grid',
    gridTemplateColumns: 'repeat(2, min-content)',
  },
}));

const GlobalActionsSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.globalActions}>
      <LoadingSkeleton height={24} variant="circular" width={24} />
      <LoadingSkeleton height={24} variant="circular" width={24} />
    </div>
  );
};

export default GlobalActionsSkeleton;
