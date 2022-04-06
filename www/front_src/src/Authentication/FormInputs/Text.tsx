import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useFormikContext, FormikValues } from 'formik';
import { equals, not, prop } from 'ramda';

import { TextField, useMemoComponent } from '@centreon/ui';

import PasswordEndAdornment from '../../Login/PasswordEndAdornment';

import { InputProps, InputType } from './models';

const Text = ({
  label,
  fieldName,
  type,
  required,
  getDisabled,
  getRequired,
}: InputProps): JSX.Element => {
  const { t } = useTranslation();

  const [isVisible, setIsVisible] = React.useState(false);

  const { values, setFieldValue, touched, errors, handleBlur } =
    useFormikContext<FormikValues>();

  const change = (event: React.ChangeEvent<HTMLInputElement>): void => {
    setFieldValue(fieldName, event.target.value);
  };

  const changeVisibility = (): void => {
    setIsVisible((currentIsVisible) => !currentIsVisible);
  };

  const value = prop(fieldName, values);

  const error = prop(fieldName, touched) ? prop(fieldName, errors) : undefined;

  const passwordEndAdornment = React.useCallback(
    (): JSX.Element | null =>
      equals(type, InputType.Password) ? (
        <PasswordEndAdornment
          changeVisibility={changeVisibility}
          isVisible={isVisible}
        />
      ) : null,
    [isVisible],
  );

  const inputType =
    equals(type, InputType.Password) && not(isVisible) ? 'password' : 'text';

  const disabled = getDisabled?.(values) || false;
  const isRequired = required || getRequired?.(values) || false;

  return useMemoComponent({
    Component: (
      <TextField
        EndAdornment={passwordEndAdornment}
        ariaLabel={t(label)}
        disabled={disabled}
        error={error as string | undefined}
        label={t(label)}
        required={isRequired}
        type={inputType}
        value={value || ''}
        onBlur={handleBlur(fieldName)}
        onChange={change}
      />
    ),
    memoProps: [error, value, isVisible, disabled, isRequired],
  });
};

export default Text;
