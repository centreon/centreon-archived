import * as React from 'react';

import { Formik } from 'formik';
import { isNil, not, pipe, prop, propOr } from 'ramda';

import { Paper, makeStyles, Typography } from '@material-ui/core';

import { LoadingSkeleton } from '@centreon/ui';

import logoCentreon from '../Navigation/Sidebar/Logo/centreon.png';
import Copyright from '../Footer/Copyright';

import useValidationSchema from './validationSchema';
import { LoginFormValues } from './models';
import useLogin from './useLogin';
import LoginForm from './Form';

const useStyles = makeStyles((theme) => ({
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
    padding: theme.spacing(2, 3),
    width: 'fit-content',
  },
}));

const initialValues: LoginFormValues = {
  alias: '',
  password: '',
};

const LoginPage = (): JSX.Element => {
  const classes = useStyles();
  const validationSchema = useValidationSchema();

  const { submitLoginForm, webVersions } = useLogin();

  const hasInstalledVersion = pipe(
    propOr(null, 'installedVersion'),
    isNil,
    not,
  );

  return (
    <div className={classes.loginBackground}>
      <Paper className={classes.loginPaper}>
        <img alt="Centreon Logo" src={logoCentreon} />
        <Formik<LoginFormValues>
          validateOnBlur
          validateOnMount
          initialValues={initialValues}
          validationSchema={validationSchema}
          onSubmit={submitLoginForm}
        >
          <LoginForm />
        </Formik>
      </Paper>
      <div className={classes.copyrightAndVersion}>
        <Copyright />
        {hasInstalledVersion(webVersions) ? (
          <Typography variant="body2">
            v{webVersions?.installedVersion}
          </Typography>
        ) : (
          <LoadingSkeleton />
        )}
      </div>
    </div>
  );
};

export default LoginPage;
