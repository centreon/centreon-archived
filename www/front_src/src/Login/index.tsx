import * as React from 'react';

import { Formik } from 'formik';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';
import { isNil } from 'ramda';

import { Paper, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { LoadingSkeleton } from '@centreon/ui';

import { areUserParametersLoadedAtom } from '../Main/useUser';
import { MainLoaderWithoutTranslation } from '../Main/MainLoader';
import Wallpaper from '../components/Wallpaper';
import useLoadWallpaper from '../components/Wallpaper/useLoadWallpaper';

import Copyright from './Copyright';
import useValidationSchema from './validationSchema';
import { LoginFormValues } from './models';
import useLogin from './useLogin';
import LoginForm from './Form';
import ExternalProviders from './ExternalProviders';
import { labelLogin } from './translatedLabels';
import Logo from './Logo';

const useStyles = makeStyles((theme) => ({
  copyrightAndVersion: {
    alignItems: 'center',
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(0.5),
  },
  loginBackground: {
    alignItems: 'center',
    backdropFilter: 'brightness(1)',
    backgroundColor: 'transparent',
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
  useLoadWallpaper();

  const areUserParametersLoaded = useAtomValue(areUserParametersLoadedAtom);

  if (areUserParametersLoaded || isNil(areUserParametersLoaded)) {
    return <MainLoaderWithoutTranslation />;
  }

  return (
    <div>
      <Wallpaper />
      <div className={classes.loginBackground}>
        <Paper className={classes.loginPaper}>
          <Logo />
          <Typography variant="h5">{t(labelLogin)}</Typography>
          <div>
            <Formik<LoginFormValues>
              initialValues={initialValues}
              validationSchema={validationSchema}
              onSubmit={submitLoginForm}
            >
              <LoginForm />
            </Formik>
            <ExternalProviders
              providersConfiguration={providersConfiguration}
            />
          </div>
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
        </Paper>
      </div>
    </div>
  );
};

export default LoginPage;
