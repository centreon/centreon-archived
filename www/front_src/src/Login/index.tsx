import { lazy, Suspense } from 'react';

import { Formik } from 'formik';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';
import { isNil } from 'ramda';

import { Paper, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { LoadingSkeleton } from '@centreon/ui';

import { areUserParametersLoadedAtom } from '../Main/useUser';
import { MainLoaderWithoutTranslation } from '../Main/MainLoader';
import useLoadWallpaper from '../components/Wallpaper/useLoadWallpaper';
import { platformVersionsAtom } from '../Main/atoms/platformVersionsAtom';

import useValidationSchema from './validationSchema';
import { LoginFormValues } from './models';
import useLogin from './useLogin';
import { labelLogin } from './translatedLabels';

const ExternalProviders = lazy(() => import('./ExternalProviders'));

const Copyright = lazy(() => import('./Copyright'));

const Wallpaper = lazy(() => import('../components/Wallpaper'));

const LoginForm = lazy(() => import('./Form'));

const Logo = lazy(() => import('./Logo'));

const useStyles = makeStyles((theme) => ({
  copyrightAndVersion: {
    alignItems: 'center',
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(0.5),
  },
  copyrightSkeleton: {
    width: theme.spacing(16),
  },
  loginBackground: {
    alignItems: 'center',
    backgroundColor: 'transparent',
    display: 'flex',
    filter: 'brightness(1)',
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

  const { submitLoginForm, providersConfiguration } = useLogin();
  useLoadWallpaper();

  const areUserParametersLoaded = useAtomValue(areUserParametersLoadedAtom);
  const platformVersions = useAtomValue(platformVersionsAtom);

  if (areUserParametersLoaded || isNil(areUserParametersLoaded)) {
    return <MainLoaderWithoutTranslation />;
  }

  return (
    <div>
      <Suspense fallback={<LoadingSkeleton />}>
        <Wallpaper />
      </Suspense>
      <div className={classes.loginBackground}>
        <Paper className={classes.loginPaper}>
          <Suspense
            fallback={
              <LoadingSkeleton height={60} variant="text" width={250} />
            }
          >
            <Logo />
          </Suspense>
          <Suspense
            fallback={
              <LoadingSkeleton height={30} variant="text" width={115} />
            }
          >
            <Typography variant="h5">{t(labelLogin)}</Typography>
          </Suspense>
          <div>
            <Formik<LoginFormValues>
              validateOnMount
              initialValues={initialValues}
              validationSchema={validationSchema}
              onSubmit={submitLoginForm}
            >
              <Suspense
                fallback={
                  <LoadingSkeleton height={45} variant="text" width={250} />
                }
              >
                <LoginForm />
              </Suspense>
            </Formik>
            <Suspense
              fallback={
                <LoadingSkeleton height={45} variant="text" width={250} />
              }
            >
              <ExternalProviders
                providersConfiguration={providersConfiguration}
              />
            </Suspense>
          </div>
          <div className={classes.copyrightAndVersion}>
            <Suspense
              fallback={
                <LoadingSkeleton
                  className={classes.copyrightSkeleton}
                  variant="text"
                />
              }
            >
              <Copyright />
            </Suspense>
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
