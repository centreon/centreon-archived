import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles, Typography } from '@material-ui/core';

import memoizeComponent from '../../../Resources/memoizedComponent';
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
