import * as React from 'react';

import { makeStyles } from '@material-ui/core';

import { PageSkeleton } from '@centreon/ui';

const useStyles = makeStyles(() => ({
  skeletonContainer: {
    width: '100%',
    height: '100vh',
  },
}));

const PageLoader = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.skeletonContainer}>
      <PageSkeleton displayHeaderAndNavigation />
    </div>
  );
};

export default PageLoader;
