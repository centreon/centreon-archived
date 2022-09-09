import { FormikValues, useFormikContext } from 'formik';
import { equals, isEmpty, path, split } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  SingleConnectedAutocompleteField,
  buildListingEndpoint,
  useMemoComponent,
} from '@centreon/ui';

import { InputPropsWithoutCategory } from './models';

const ConnectedAutocomplete = ({
  getDisabled,
  required,
  getRequired,
  fieldName,
  label,
  filterKey = 'name',
  endpoint,
  change,
  additionalMemoProps,
}: InputPropsWithoutCategory): JSX.Element => {
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

  const fieldNamePath = split('.', fieldName);

  const changeAutocomplete = (_, value): void => {
    if (change) {
      change({ setFieldValue, value });

      return;
    }

    setFieldValue(fieldName, value);

    if (path(fieldNamePath, touched)) {
      return;
    }

    setFieldTouched(fieldName, true);
  };

  const blur = (): void => setFieldTouched(fieldName, true);

  const isOptionEqualToValue = (option, value): boolean => {
    return isEmpty(value) ? false : equals(option[filterKey], value[filterKey]);
  };

  const value = path(fieldNamePath, values);

  const error = path(fieldNamePath, touched)
    ? path(fieldNamePath, errors)
    : undefined;

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
        value={value ?? null}
        onBlur={blur}
        onChange={changeAutocomplete}
      />
    ),
    memoProps: [value, error, disabled, isRequired, additionalMemoProps],
  });
};

export default ConnectedAutocomplete;
