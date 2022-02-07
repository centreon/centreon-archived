import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import logoCentreon from '../Navigation/Sidebar/Logo/centreon.png';
import { labelCentreonLogo } from '../Login/translatedLabels';

import { labelCentreonIsLoading } from './translatedLabels';

const useStyles = makeStyles((theme) => ({
  loader: {
    alignItems: 'center',
    backgroundColor: theme.palette.background.default,
    display: 'flex',
    flexDirection: 'column',
    height: '100vh',
    justifyContent: 'center',
    rowGap: theme.spacing(2),
    width: '100%',
  },
}));

export const MainLoader = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div className={classes.loader}>
      <img alt={t(labelCentreonLogo)} src={logoCentreon} />
      <Typography>{t(labelCentreonIsLoading)}</Typography>
    </div>
  );
};

export const MainLoaderWithoutTranslation = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.loader}>
      <img alt={labelCentreonLogo} src={logoCentreon} />
      <Typography>{labelCentreonIsLoading}</Typography>
    </div>
  );
};
