import { useEffect } from 'react';

import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';
import { isNil, not } from 'ramda';
import { useNavigate } from 'react-router';
import { Formik } from 'formik';

import { Paper, Typography } from '@mui/material';
import { makeStyles } from '@mui/styles';

import routeMap from '../reactRoutes/routeMap';
import Logo from '../Login/Logo';

import { passwordResetInformationsAtom } from './passwordResetInformationsAtom';
import { labelResetYourPassword } from './translatedLabels';
import { ResetPasswordValues } from './models';
import useResetPassword from './useResetPassword';
import Form from './Form';

const useStyles = makeStyles((theme) => ({
  container: {
    alignItems: 'center',
    backgroundColor: theme.palette.background.default,
    display: 'flex',
    flexDirection: 'column',
    height: '100vh',
    justifyContent: 'center',
    rowGap: theme.spacing(2),
    width: '100vw',
  },
  paper: {
    padding: theme.spacing(4, 3),
  },
}));

const initialValues = {
  newPassword: '',
  newPasswordConfirmation: '',
  oldPassword: '',
};

const ResetPassword = (): JSX.Element | null => {
  const classes = useStyles();
  const { t } = useTranslation();
  const navigate = useNavigate();

  const passwordResetInformations = useAtomValue(passwordResetInformationsAtom);

  const { submitResetPassword, validationSchema } = useResetPassword();

  useEffect(() => {
    if (
      not(isNil(passwordResetInformations)) &&
      passwordResetInformations?.alias
    ) {
      return;
    }

    navigate(routeMap.login);
  }, [passwordResetInformations]);

  if (
    isNil(passwordResetInformations) ||
    not(passwordResetInformations?.alias)
  ) {
    return null;
  }

  return (
    <div className={classes.container}>
      <Logo />
      <Paper className={classes.paper}>
        <Typography variant="h4">{t(labelResetYourPassword)}</Typography>
        <Formik<ResetPasswordValues>
          initialValues={initialValues}
          validationSchema={validationSchema}
          onSubmit={submitResetPassword}
        >
          <Form />
        </Formik>
      </Paper>
    </div>
  );
};

export default ResetPassword;
