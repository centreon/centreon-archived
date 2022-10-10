import { FC } from 'react';

import { useTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';

import { Box } from '@mui/material';

import { FallbackPage } from '@centreon/ui';

import {
  labelAuthenticationDenied,
  labelYouAreNotAbleToLogIn,
} from './translatedLabels';

const useStyles = makeStyles()({
  pageContainer: {
    height: '100vh',
    width: '100vw',
  },
});

const AuthenticationDenied: FC = () => {
  const { classes } = useStyles();
  const { t } = useTranslation();

  return (
    <Box className={classes.pageContainer}>
      <FallbackPage
        message={t(labelYouAreNotAbleToLogIn)}
        title={t(labelAuthenticationDenied)}
      />
    </Box>
  );
};

export default AuthenticationDenied;
