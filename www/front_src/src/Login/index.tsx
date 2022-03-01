import * as React from 'react';

import { Formik } from 'formik';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';
import { isNil } from 'ramda';

import { Paper, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { LoadingSkeleton } from '@centreon/ui';

import logoCentreon from '../Navigation/Sidebar/Logo/centreon.png';
import Copyright from '../Footer/Copyright';
import { areUserParametersLoadedAtom } from '../Main/useUser';
import { MainLoaderWithoutTranslation } from '../Main/MainLoader';

import useValidationSchema from './validationSchema';
import { LoginFormValues } from './models';
import useLogin from './useLogin';
import LoginForm from './Form';
import { labelCentreonLogo, labelLogin } from './translatedLabels';
import ExternalProviders from './ExternalProviders';

const useStyles = makeStyles((theme) => ({
  centreonLogo: {
    height: 'auto',
    width: 'auto',
  },
  copyrightAndVersion: {
    alignItems: 'center',
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(0.5),
  },
  loginBackground: {
    alignItems: 'center',
    backgroundColor: theme.palette.background.default,
    display: 'flex',
    flexDirection: 'column',
    height: '100vh',
    justifyContent: 'center',
    rowGap: theme.spacing(2),
    width: '100%',
  },
  loginPaper: {
    alignItems: 'center',
    display: 'grid',
    flexDirection: 'column',
    justifyItems: 'center',
    minWidth: theme.spacing(30),
    padding: theme.spacing(4, 5),
    rowGap: theme.spacing(4),
    width: '17%',
  },
}));

const initialValues: LoginFormValues = {
  alias: '',
  password: '',
};

const LoginPage = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const validationSchema = useValidationSchema();

  const { submitLoginForm, platformVersions, providersConfiguration } =
    useLogin();
  const areUserParametersLoaded = useAtomValue(areUserParametersLoadedAtom);

  if (areUserParametersLoaded || isNil(areUserParametersLoaded)) {
    return <MainLoaderWithoutTranslation />;
  }

  return (
    <div className={classes.loginBackground}>
      <img
        alt={t(labelCentreonLogo)}
        aria-label={t(labelCentreonLogo)}
        className={classes.centreonLogo}
        src={logoCentreon}
      />
      <Paper className={classes.loginPaper}>
        <Typography variant="h5">{t(labelLogin)}</Typography>
        <div>
          <Formik<LoginFormValues>
            initialValues={initialValues}
            validationSchema={validationSchema}
            onSubmit={submitLoginForm}
          >
            <LoginForm />
          </Formik>
          <ExternalProviders providersConfiguration={providersConfiguration} />
        </div>
      </Paper>
      <div className={classes.copyrightAndVersion}>
        <Copyright />
        {isNil(platformVersions) ? (
          <LoadingSkeleton variant="text" width="40%" />
        ) : (
          <Typography variant="body2">
            v. {platformVersions?.web.version}
          </Typography>
        )}
      </div>
    </div>
  );
};

export default LoginPage;
