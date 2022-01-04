import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { isEmpty, not, prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Button, CircularProgress } from '@mui/material';

import makeStyles from '@mui/styles/makeStyles';

import { TextField } from '@centreon/ui';

import { labelAlias, labelLogin, labelPassword } from './translatedLabels';

const aliasFieldName = 'alias';
const passwordFieldName = 'password';

const useStyles = makeStyles((theme) => ({
  form: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(2),
    width: '100%',
  },
}));

const getTouchedError = ({ fieldName, errors, touched }): string | undefined =>
  prop(fieldName, touched) && prop(fieldName, errors);

const LoginForm = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const {
    values,
    handleChange,
    errors,
    touched,
    handleBlur,
    dirty,
    isSubmitting,
    handleSubmit,
  } = useFormikContext<FormikValues>();

  const aliasValue = prop(aliasFieldName, values);
  const aliasError = getTouchedError({
    errors,
    fieldName: aliasFieldName,
    touched,
  });
  const passwordValue = prop(passwordFieldName, values);
  const passwordError = getTouchedError({
    errors,
    fieldName: passwordFieldName,
    touched,
  });
  const isDisabled = not(isEmpty(errors)) || isSubmitting || not(dirty);

  return (
    <form className={classes.form} onSubmit={handleSubmit}>
      <TextField
        fullWidth
        required
        ariaLabel={t(labelAlias)}
        error={aliasError}
        label={t(labelAlias)}
        value={aliasValue || ''}
        onBlur={handleBlur(aliasFieldName)}
        onChange={handleChange(aliasFieldName)}
      />
      <TextField
        fullWidth
        required
        ariaLabel={t(labelPassword)}
        error={passwordError}
        label={t(labelPassword)}
        type="password"
        value={passwordValue || ''}
        onBlur={handleBlur(passwordFieldName)}
        onChange={handleChange(passwordFieldName)}
      />
      <Button
        fullWidth
        aria-label={t(labelLogin)}
        color="primary"
        disabled={isDisabled}
        endIcon={isSubmitting && <CircularProgress color="inherit" size={20} />}
        type="submit"
        variant="contained"
      >
        {t(labelLogin)}
      </Button>
    </form>
  );
};

export default LoginForm;
