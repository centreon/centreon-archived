import { ChangeEvent, useCallback, useState } from 'react';

import { useTranslation } from 'react-i18next';
import { useFormikContext, FormikValues } from 'formik';
import { equals, not, path, split } from 'ramda';

import { TextField, useMemoComponent } from '@centreon/ui';

import PasswordEndAdornment from '../../Login/PasswordEndAdornment';

import { InputPropsWithoutCategory, InputType } from './models';

const Text = ({
  label,
  fieldName,
  type,
  required,
  getDisabled,
  getRequired,
  change,
  additionalMemoProps,
}: InputPropsWithoutCategory): JSX.Element => {
  const { t } = useTranslation();

  const [isVisible, setIsVisible] = useState(false);

  const { values, setFieldValue, touched, errors, handleBlur } =
    useFormikContext<FormikValues>();

  const fieldNamePath = split('.', fieldName);

  const changeText = (event: ChangeEvent<HTMLInputElement>): void => {
    if (change) {
      change({ setFieldValue, value: event.target.value });

      return;
    }

    setFieldValue(fieldName, event.target.value);
  };

  const changeVisibility = (): void => {
    setIsVisible((currentIsVisible) => !currentIsVisible);
  };

  const value = path(fieldNamePath, values);

  const error = path(fieldNamePath, touched)
    ? path(fieldNamePath, errors)
    : undefined;

  const passwordEndAdornment = useCallback(
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
        fullWidth
        EndAdornment={passwordEndAdornment}
        ariaLabel={t(label)}
        disabled={disabled}
        error={error as string | undefined}
        label={t(label)}
        required={isRequired}
        type={inputType}
        value={value || ''}
        onBlur={handleBlur(fieldName)}
        onChange={changeText}
      />
    ),
    memoProps: [
      error,
      value,
      isVisible,
      disabled,
      isRequired,
      additionalMemoProps,
    ],
  });
};

export default Text;
