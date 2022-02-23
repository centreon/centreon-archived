import * as React from 'react';

import { Formik } from 'formik';
import { always, cond, isNil, lte } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import { Paper, Typography, useTheme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { LoadingSkeleton } from '@centreon/ui';

import logoCentreon from '../assets/centreon.png';
import Copyright from '../Footer/Copyright';
import { areUserParametersLoadedAtom } from '../Main/useUser';
import { MainLoaderWithoutTranslation } from '../Main/MainLoader';
import Wallpaper from '../components/Wallpaper';
import centreonWallpaperXl from '../assets/centreon-wallpaper-xl.jpg';
import centreonWallpaperLg from '../assets/centreon-wallpaper-lg.jpg';
import centreonWallpaperSm from '../assets/centreon-wallpaper-sm.jpg';
import { loadImageDerivedAtom } from '../components/Wallpaper/loadImageAtom';

import useValidationSchema from './validationSchema';
import { LoginFormValues } from './models';
import useLogin from './useLogin';
import LoginForm from './Form';
import { labelCentreonLogo, labelLogin } from './translatedLabels';

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

  const { submitLoginForm, platformVersions } = useLogin();
  const areUserParametersLoaded = useAtomValue(areUserParametersLoadedAtom);

  const loadImage = useUpdateAtom(loadImageDerivedAtom);

  const theme = useTheme();

  const imagePath = React.useMemo(
    (): string =>
      cond<number, string>([
        [lte(theme.breakpoints.values.xl), always(centreonWallpaperXl)],
        [lte(theme.breakpoints.values.lg), always(centreonWallpaperLg)],
        [lte(theme.breakpoints.values.sm), always(centreonWallpaperSm)],
      ])(window.screen.width),
    [],
  );

  React.useEffect((): void => {
    loadImage(imagePath);
  }, []);

  if (areUserParametersLoaded || isNil(areUserParametersLoaded)) {
    return <MainLoaderWithoutTranslation />;
  }

  return (
    <div>
      <Wallpaper />
      <div className={classes.loginBackground}>
        <Paper className={classes.loginPaper}>
          <img
            alt={t(labelCentreonLogo)}
            aria-label={t(labelCentreonLogo)}
            className={classes.centreonLogo}
            src={logoCentreon}
          />
          <Typography variant="h5">{t(labelLogin)}</Typography>
          <Formik<LoginFormValues>
            initialValues={initialValues}
            validationSchema={validationSchema}
            onSubmit={submitLoginForm}
          >
            <LoginForm />
          </Formik>
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
