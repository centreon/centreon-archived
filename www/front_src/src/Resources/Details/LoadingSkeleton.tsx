import * as React from 'react';

import { styled } from '@mui/material';
import { makeStyles } from '@mui/styles';

import { LoadingSkeleton } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  loadingSkeleton: {
    display: 'grid',
    gridTemplateRows: 'repeat(3, 67px)',
    rowGap: theme.spacing(2),
  },
}));

const CardSkeleton = styled(LoadingSkeleton)(() => ({
  transform: 'none',
}));

const DetailsLoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.loadingSkeleton}>
      <CardSkeleton height="100%" />
      <CardSkeleton height="100%" />
      <CardSkeleton height="100%" />
    </div>
  );
};

export default DetailsLoadingSkeleton;
