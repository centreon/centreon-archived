import React from 'react';

import { useTranslation } from 'react-i18next';
import { Alert } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { labelYouAreNotAllowedToSeeThisPage } from './translatedLabels';

const useStyles = makeStyles((theme) => ({
  alert: {
    marginTop: theme.spacing(4),
    width: 'fit-content',
  },
  page: {
    display: 'flex',
    justifyContent: 'center',
  },
}));

const NotAllowedPage = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div className={classes.page}>
      <Alert className={classes.alert} severity="error">
        {t(labelYouAreNotAllowedToSeeThisPage)}
      </Alert>
    </div>
  );
};

export default NotAllowedPage;
