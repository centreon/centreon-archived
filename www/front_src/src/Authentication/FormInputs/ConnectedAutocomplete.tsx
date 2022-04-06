import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  SingleConnectedAutocompleteField,
  buildListingEndpoint,
  useMemoComponent,
} from '@centreon/ui';

import { contactTemplatesEndpoint } from '../api/endpoints';

import { InputProps } from './models';

const ConnectedAutocomplete = ({
  getDisabled,
  required,
  getRequired,
  fieldName,
  label,
}: InputProps): JSX.Element => {
  const { t } = useTranslation();

  const { values, touched, errors, setFieldValue, setFieldTouched } =
    useFormikContext<FormikValues>();

  const getEndpoint = (parameters): string =>
    buildListingEndpoint({
      baseEndpoint: contactTemplatesEndpoint,
      parameters,
    });

  const change = (value): void => {
    setFieldTouched(fieldName);
    setFieldValue(fieldName, value);
  };

  const value = prop(fieldName, values);

  const error = prop(fieldName, touched) ? prop(fieldName, errors) : undefined;

  const disabled = getDisabled?.(values) || false;
  const isRequired = required || getRequired?.(values) || false;

  return useMemoComponent({
    Component: (
      <SingleConnectedAutocompleteField
        disableClearable={false}
        disabled={disabled}
        error={error}
        field="name"
        getEndpoint={getEndpoint}
        initialPage={1}
        label={t(label)}
        name={fieldName}
        required={isRequired}
        value={value}
        onChange={change}
      />
    ),
    memoProps: [value, error, disabled, required],
  });
};

export default ConnectedAutocomplete;
