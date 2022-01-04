import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { Divider, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import centreonLogo from '../img/centreon.png';
import { labelCentreonLogo } from '../Login/translatedLabels';

import { label404, labelThisPageCouldNotBeFound } from './translatedLabels';

const useStyles = makeStyles((theme) => ({
  logo: {
    height: 'auto',
    width: 'auto',
  },
  page: {
    alignItems: 'center',
    display: 'grid',
    gridTemplateColumns: '1fr',
    height: '100%',
    justifyItems: 'center',
    width: '100%',
  },
  wrapper: {
    alignItems: 'center',
    columnGap: theme.spacing(3),
    display: 'flex',
    flexDirection: 'row',
    height: theme.spacing(6),
    marginTop: theme.spacing(3),
  },
}));

const NotFoundPage = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div className={classes.page}>
      <div>
        <img
          alt={t(labelCentreonLogo)}
          className={classes.logo}
          src={centreonLogo}
        />
        <div className={classes.wrapper}>
          <Typography className={classes[404]} variant="h6">
            {label404}
          </Typography>
          <Divider flexItem orientation="vertical" />
          <Typography>{t(labelThisPageCouldNotBeFound)}</Typography>
        </div>
      </div>
    </div>
  );
};

export default NotFoundPage;
