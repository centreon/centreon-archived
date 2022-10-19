import { useTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';

import { Typography } from '@mui/material';

import logoCentreon from '../assets/logo-centreon-colors.png';
import { labelCentreonLogo } from '../Login/translatedLabels';

import { labelCentreonIsLoading } from './translatedLabels';

const useStyles = makeStyles()((theme) => ({
  loader: {
    alignItems: 'center',
    backgroundColor: theme.palette.background.paper,
    display: 'flex',
    flexDirection: 'column',
    height: '100vh',
    justifyContent: 'center',
    rowGap: theme.spacing(2),
    width: '100%',
  },
}));

export const MainLoader = (): JSX.Element => {
  const { classes } = useStyles();
  const { t } = useTranslation();

  return (
    <div className={classes.loader}>
      <img alt={t(labelCentreonLogo)} src={logoCentreon} />
      <Typography>{t(labelCentreonIsLoading)}</Typography>
    </div>
  );
};

export const MainLoaderWithoutTranslation = (): JSX.Element => {
  const { classes } = useStyles();

  return (
    <div className={classes.loader}>
      <img alt={labelCentreonLogo} src={logoCentreon} />
      <Typography>{labelCentreonIsLoading}</Typography>
    </div>
  );
};
