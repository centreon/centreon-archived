import * as React from 'react';

import { Divider, makeStyles } from '@material-ui/core';
import { Skeleton, SkeletonProps } from '@material-ui/lab';

const useStyles = makeStyles((theme) => ({
  buttonsGroup: {
    columnGap: theme.spacing(2),
    display: 'grid',
    gridTemplateColumns: 'repeat(2, 77px)',
    marginTop: theme.spacing(1),
  },
  formContainer: {
    margin: theme.spacing(2, 1),
  },
  formGroup: {
    marginBottom: theme.spacing(1),
    marginTop: theme.spacing(0.5),
  },
  passwordCaseGroup: {
    columnGap: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: 'repeat(2, 1fr)',
    marginTop: theme.spacing(1),
    width: theme.spacing(50),
  },
  passwordExpirationAndBlockingGroups: {
    display: 'flex',
    flexDirection: 'column',
    marginTop: theme.spacing(1),
    rowGap: theme.spacing(1),
  },
}));

const commonSkeletonProps: SkeletonProps = {
  animation: 'wave',
  variant: 'rect',
};

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.formContainer}>
      <div className={classes.formGroup}>
        <Skeleton {...commonSkeletonProps} height={32} width={230} />
        <div className={classes.passwordCaseGroup}>
          <Skeleton {...commonSkeletonProps} height={51} width="100%" />
          <Skeleton {...commonSkeletonProps} height={51} width="100%" />
        </div>
      </div>
      <Divider />
      <div className={classes.formGroup}>
        <Skeleton {...commonSkeletonProps} height={32} width={260} />
        <div className={classes.passwordExpirationAndBlockingGroups}>
          <Skeleton {...commonSkeletonProps} height={53} width="40%" />
          <Skeleton {...commonSkeletonProps} height={53} width="90%" />
          <Skeleton {...commonSkeletonProps} height={24} width="35%" />
        </div>
      </div>
      <Divider />
      <div className={classes.formGroup}>
        <Skeleton {...commonSkeletonProps} height={32} width={260} />
        <div className={classes.passwordExpirationAndBlockingGroups}>
          <Skeleton {...commonSkeletonProps} height={62} width="70%" />
          <Skeleton {...commonSkeletonProps} height={70} width="95%" />
        </div>
      </div>
      <Divider />
      <div className={classes.buttonsGroup}>
        <Skeleton {...commonSkeletonProps} height={30} width="100%" />
        <Skeleton {...commonSkeletonProps} height={30} width="80%" />
      </div>
    </div>
  );
};

export default LoadingSkeleton;
