import * as React from 'react';

import { Formik } from 'formik';

import { Paper, makeStyles, Typography } from '@material-ui/core';

import logoCentreon from '../Navigation/Sidebar/Logo/centreon.png';

import useValidationSchema from './validationSchema';
import { Login, LoginFormValues } from './models';
import useLogin from './useLogin';
import LoginForm from './Form';

const useStyles = makeStyles((theme) => ({
  loginBackground: {
    alignItems: 'center',
    backgroundColor: theme.palette.background.default,
    display: 'flex',
    height: '100vh',
    justifyContent: 'center',
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

  const { submitLoginForm } = useLogin();

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
    </div>
  );
};

export default LoginPage;
