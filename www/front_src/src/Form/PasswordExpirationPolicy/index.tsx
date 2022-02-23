import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import memoizeComponent from '../../Resources/memoizedComponent';
import { labelPasswordExpirationPolicy } from '../../translatedLabels';

import PasswordExpiration from './PasswordExpiration';
import CanReusePasswords from './CanReusePasswords';
import TimeBeforeNewPassword from './TimeBeforeNewPassword';

const useStyles = makeStyles((theme) => ({
  passwordExpirationPolicy: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(1.5),
  },
}));

const PasswordExpirationPolicy = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div>
      <Typography variant="h5">{t(labelPasswordExpirationPolicy)}</Typography>
      <div className={classes.passwordExpirationPolicy}>
        <PasswordExpiration />
        <TimeBeforeNewPassword />
        <CanReusePasswords />
      </div>
    </div>
  );
};

export default memoizeComponent({
  Component: PasswordExpirationPolicy,
  memoProps: [],
});
