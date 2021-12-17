import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { isEmpty, not, prop } from 'ramda';

import { Button, CircularProgress, makeStyles } from '@material-ui/core';

import { TextField } from '@centreon/ui';

import { labelAlias, labelLogin, labelPassword } from './translatedLabels';

const aliasFieldName = 'alias';
const passwordFieldName = 'password';

const useStyles = makeStyles((theme) => ({
  form: {
    display: 'flex',
    flexDirection: 'column',
    marginTop: theme.spacing(3),
    rowGap: theme.spacing(2),
  },
}));

const getTouchedError = ({ fieldName, errors, touched }): string | undefined =>
  prop(fieldName, touched) && prop(fieldName, errors);

const LoginForm = (): JSX.Element => {
  const classes = useStyles();
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
        ariaLabel={labelAlias}
        error={aliasError}
        label={labelAlias}
        value={aliasValue || ''}
        onBlur={handleBlur(aliasFieldName)}
        onChange={handleChange(aliasFieldName)}
      />
      <TextField
        ariaLabel={labelPassword}
        error={passwordError}
        label={labelPassword}
        type="password"
        value={passwordValue || ''}
        onBlur={handleBlur(passwordFieldName)}
        onChange={handleChange(passwordFieldName)}
      />
      <Button
        fullWidth
        aria-label={labelLogin}
        color="primary"
        disabled={isDisabled}
        endIcon={isSubmitting && <CircularProgress color="inherit" size={20} />}
        type="submit"
        variant="contained"
      >
        {labelLogin}
      </Button>
    </form>
  );
};

export default LoginForm;
