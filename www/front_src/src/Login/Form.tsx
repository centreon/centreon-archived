import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { isEmpty, not, prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Button, CircularProgress } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import PersonIcon from '@mui/icons-material/Person';
import LockIcon from '@mui/icons-material/Lock';

import { TextField } from '@centreon/ui';

import { labelAlias, labelConnect, labelPassword } from './translatedLabels';
import PasswordEndAdornment from './PasswordEndAdornment';

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
  const [isVisible, setIsVisible] = React.useState(false);
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

  const changeVisibility = (): void => {
    setIsVisible((currentIsVisible) => !currentIsVisible);
  };

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

  const passwordEndAdornment = React.useCallback(
    (): JSX.Element => (
      <PasswordEndAdornment
        changeVisibility={changeVisibility}
        isVisible={isVisible}
      />
    ),
    [isVisible],
  );

  return (
    <form className={classes.form} onSubmit={handleSubmit}>
      <TextField
        fullWidth
        required
        StartAdornment={PersonIcon}
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
        EndAdornment={passwordEndAdornment}
        StartAdornment={LockIcon}
        ariaLabel={t(labelPassword)}
        error={passwordError}
        label={t(labelPassword)}
        type={isVisible ? 'text' : 'password'}
        value={passwordValue || ''}
        onBlur={handleBlur(passwordFieldName)}
        onChange={handleChange(passwordFieldName)}
      />
      <Button
        fullWidth
        aria-label={t(labelConnect)}
        color="primary"
        disabled={isDisabled}
        endIcon={isSubmitting && <CircularProgress color="inherit" size={20} />}
        type="submit"
        variant="contained"
      >
        {t(labelConnect)}
      </Button>
    </form>
  );
};

export default LoginForm;
