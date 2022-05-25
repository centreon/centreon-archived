import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { equals, isEmpty, prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  SingleConnectedAutocompleteField,
  buildListingEndpoint,
  useMemoComponent,
} from '@centreon/ui';

import { InputProps } from './models';

const ConnectedAutocomplete = ({
  getDisabled,
  required,
  getRequired,
  fieldName,
  label,
  filterKey = 'name',
  endpoint,
}: InputProps): JSX.Element => {
  const { t } = useTranslation();

  const { values, touched, errors, setFieldValue, setFieldTouched } =
    useFormikContext<FormikValues>();

  const getEndpoint = (parameters): string =>
    buildListingEndpoint({
      baseEndpoint: endpoint,
      parameters: {
        ...parameters,
        sort: { [filterKey]: 'ASC' },
      },
    });

  const change = (_, value): void => {
    setFieldValue(fieldName, value);

    if (prop(fieldName, touched)) {
      return;
    }
    setFieldTouched(fieldName, true);
  };

  const blur = (): void => setFieldTouched(fieldName, true);

  const isOptionEqualToValue = (option, value): boolean => {
    return isEmpty(value) ? false : equals(option[filterKey], value[filterKey]);
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
        field={filterKey}
        getEndpoint={getEndpoint}
        initialPage={1}
        isOptionEqualToValue={isOptionEqualToValue}
        label={t(label)}
        name={fieldName}
        required={isRequired}
        value={value}
        onBlur={blur}
        onChange={change}
      />
    ),
    memoProps: [value, error, disabled, required],
  });
};

export default ConnectedAutocomplete;
