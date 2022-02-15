import * as React from 'react';

import {
  FormikErrors,
  FormikTouched,
  FormikValues,
  useFormikContext,
} from 'formik';
import { equals, isEmpty, not, prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@mui/styles';
import { Button, CircularProgress, Divider } from '@mui/material';

import { TextField } from '@centreon/ui';

import PasswordEndAdornment from '../Login/PasswordEndAdornment';

import {
  labelCurrentPassword,
  labelNewPassword,
  labelNewPasswordConfirmation,
  labelResetPassword,
} from './translatedLabels';

const oldPasswordFieldName = 'oldPassword';
const newPasswordFieldName = 'newPassword';
const newPasswordConfirmationFieldName = 'newPasswordConfirmation';

interface GetErrorProps {
  errors: FormikErrors<FormikValues>;
  touched: FormikTouched<FormikValues>;
}

const contentLayout = [
  {
    getError: ({ errors, touched }: GetErrorProps): string | undefined =>
      prop(oldPasswordFieldName, touched)
        ? (prop(oldPasswordFieldName, errors) as string | undefined)
        : undefined,
    getValue: (values: FormikValues): string =>
      prop(oldPasswordFieldName, values),
    label: labelCurrentPassword,
    name: oldPasswordFieldName,
  },
  {
    getError: ({ errors, touched }: GetErrorProps): string | undefined =>
      prop(newPasswordFieldName, touched)
        ? (prop(newPasswordFieldName, errors) as string | undefined)
        : undefined,
    getValue: (values: FormikValues): string =>
      prop(newPasswordFieldName, values),
    label: labelNewPassword,
    name: newPasswordFieldName,
  },
  {
    getError: ({ errors, touched }: GetErrorProps): string | undefined =>
      prop(newPasswordConfirmationFieldName, touched)
        ? (prop(newPasswordConfirmationFieldName, errors) as string | undefined)
        : undefined,
    getValue: (values: FormikValues): string =>
      prop(newPasswordConfirmationFieldName, values),
    label: labelNewPasswordConfirmation,
    name: newPasswordConfirmationFieldName,
  },
];

const useStyles = makeStyles((theme) => ({
  form: {
    display: 'flex',
    flexDirection: 'column',
    marginTop: theme.spacing(3),
    rowGap: theme.spacing(2),
  },
}));

const Form = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const [passwordVisibility, setPasswordVisibility] = React.useState({
    [newPasswordConfirmationFieldName]: false,
    [newPasswordFieldName]: false,
    [oldPasswordFieldName]: false,
  });

  const {
    handleSubmit,
    values,
    handleChange,
    errors,
    touched,
    handleBlur,
    isSubmitting,
    dirty,
  } = useFormikContext<FormikValues>();

  const changeVisibility = (fieldName: string): void => {
    setPasswordVisibility((currentPasswordVisibility) => ({
      ...currentPasswordVisibility,
      [fieldName]: !currentPasswordVisibility[fieldName],
    }));
  };

  const isDisabled = not(isEmpty(errors)) || isSubmitting || not(dirty);

  return (
    <form className={classes.form} onSubmit={handleSubmit}>
      {contentLayout.map(({ name, label, getValue, getError }): JSX.Element => {
        const passwordEndAdornment = (): JSX.Element => (
          <PasswordEndAdornment
            changeVisibility={(): void => changeVisibility(name)}
            isVisible={passwordVisibility[name]}
          />
        );

        return (
          <React.Fragment key={name}>
            <TextField
              fullWidth
              required
              EndAdornment={passwordEndAdornment}
              ariaLabel={t(label)}
              error={getError({ errors, touched })}
              label={t(label)}
              name={name}
              type={passwordVisibility[name] ? 'text' : 'password'}
              value={getValue(values)}
              onBlur={handleBlur(name)}
              onChange={handleChange(name)}
            />
            {equals(name, oldPasswordFieldName) && <Divider />}
          </React.Fragment>
        );
      })}

      <Button
        fullWidth
        aria-label={t(labelResetPassword)}
        color="primary"
        disabled={isDisabled}
        endIcon={isSubmitting && <CircularProgress color="inherit" size={20} />}
        type="submit"
        variant="contained"
      >
        {t(labelResetPassword)}
      </Button>
    </form>
  );
};

export default Form;
