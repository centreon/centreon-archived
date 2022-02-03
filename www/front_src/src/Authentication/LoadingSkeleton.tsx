import * as React from 'react';

import { Divider } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { LoadingSkeleton } from '@centreon/ui';

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
    width: theme.spacing(60),
  },
  passwordExpirationAndBlockingGroups: {
    display: 'flex',
    flexDirection: 'column',
    marginTop: theme.spacing(1),
    rowGap: theme.spacing(1),
  },
}));

const AuthenticationLoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.formContainer}>
      <div className={classes.formGroup}>
        <LoadingSkeleton height={32} width={230} />
        <div className={classes.passwordCaseGroup}>
          <LoadingSkeleton height={51} width="100%" />
          <LoadingSkeleton height={51} width="100%" />
        </div>
      </div>
      <Divider />
      <div className={classes.formGroup}>
        <LoadingSkeleton height={32} width={260} />
        <div className={classes.passwordExpirationAndBlockingGroups}>
          <LoadingSkeleton height={53} width="40%" />
          <LoadingSkeleton height={53} width="90%" />
          <LoadingSkeleton height={24} width="35%" />
        </div>
      </div>
      <Divider />
      <div className={classes.formGroup}>
        <LoadingSkeleton height={32} width={260} />
        <div className={classes.passwordExpirationAndBlockingGroups}>
          <LoadingSkeleton height={62} width="70%" />
          <LoadingSkeleton height={70} width="95%" />
        </div>
      </div>
      <Divider />
      <div className={classes.buttonsGroup}>
        <LoadingSkeleton height={30} width="100%" />
        <LoadingSkeleton height={30} width="80%" />
      </div>
    </div>
  );
};

export default AuthenticationLoadingSkeleton;
