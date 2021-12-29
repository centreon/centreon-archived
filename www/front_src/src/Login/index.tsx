import * as React from 'react';

import { Formik } from 'formik';
import { isNil } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';

import { Paper, makeStyles, Typography } from '@material-ui/core';

import { LoadingSkeleton } from '@centreon/ui';

import logoCentreon from '../Navigation/Sidebar/Logo/centreon.png';
import Copyright from '../Footer/Copyright';
import { areUserParametersLoadedAtom } from '../Main/useUser';
import MainLoader from '../Main/MainLoader';

import useValidationSchema from './validationSchema';
import { LoginFormValues } from './models';
import useLogin from './useLogin';
import LoginForm from './Form';
import { labelCentreonLogo } from './translatedLabels';

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
    display: 'grid',
    flexDirection: 'column',
    justifyItems: 'center',
    minWidth: theme.spacing(30),
    padding: theme.spacing(4, 5),
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

  if (areUserParametersLoaded || isNil(areUserParametersLoaded)) {
    return <MainLoader />;
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
        <Formik<LoginFormValues>
          initialValues={initialValues}
          validationSchema={validationSchema}
          onSubmit={submitLoginForm}
        >
          <LoginForm />
        </Formik>
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
