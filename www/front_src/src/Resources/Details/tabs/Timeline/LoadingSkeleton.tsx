import * as React from 'react';

import makeStyles from '@mui/styles/makeStyles';
import { Skeleton } from '@mui/material';

const useStyles = makeStyles((theme) => {
  return {
    skeleton: {
      display: 'grid',
      gridGap: theme.spacing(1),
    },
  };
});

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.skeleton}>
      <Skeleton height={20} style={{ transform: 'none' }} width={125} />
      <Skeleton height={100} style={{ transform: 'none' }} />
      <Skeleton height={100} style={{ transform: 'none' }} />
    </div>
  );
};

export default LoadingSkeleton;
