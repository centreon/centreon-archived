import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useFormikContext, FormikValues } from 'formik';
import { equals, prop } from 'ramda';

import { TextField, useMemoComponent } from '@centreon/ui';

import { InputProps, InputType } from '../models';

const Text = ({
  label,
  fieldName,
  getDisabled,
  type,
}: InputProps): JSX.Element => {
  const { t } = useTranslation();

  const { values, setFieldValue, touched, errors, handleBlur } =
    useFormikContext<FormikValues>();

  const change = (event: React.ChangeEvent<HTMLInputElement>): void => {
    setFieldValue(fieldName, event.target.value);
  };

  const value = prop(fieldName, values);
  const disabled = getDisabled?.(values);

  const error = prop(fieldName, touched) ? prop(fieldName, errors) : undefined;

  return useMemoComponent({
    Component: (
      <TextField
        ariaLabel={t(label)}
        disabled={disabled}
        error={error as string | undefined}
        label={t(label)}
        type={equals(type, InputType.Password) ? 'password' : 'text'}
        value={value || ''}
        onBlur={handleBlur(fieldName)}
        onChange={change}
      />
    ),
    memoProps: [error, value, disabled],
  });
};

export default Text;
