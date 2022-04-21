import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { equals, isEmpty, isNil, map, prop, type } from 'ramda';
import { useTranslation } from 'react-i18next';

import { FormHelperText, Stack } from '@mui/material';

import {
  MultiAutocompleteField,
  SelectEntry,
  useMemoComponent,
} from '@centreon/ui';

import { labelPressEnterToAccept } from '../translatedLabels';

import { InputProps } from './models';

const Multiple = ({ fieldName, label, required }: InputProps): JSX.Element => {
  const { t } = useTranslation();

  const [inputText, setInputText] = React.useState('');

  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();

  const change = (_, newValues): void => {
    const normalizedNewValues = map((newValue: SelectEntry | string) => {
      if (equals(type(newValue), 'String')) {
        return newValue;
      }

      return prop('name', newValue as SelectEntry);
    }, newValues);
    setFieldValue(fieldName, normalizedNewValues);
  };

  const selectedValues = prop(fieldName, values);

  const getError = (): Array<string> | undefined => {
    const error = (prop(fieldName, errors) as Array<string> | undefined)
      ?.map((errorText, index) => {
        if (isNil(errorText)) {
          return undefined;
        }

        return `${selectedValues[index]}: ${errorText}`;
      })
      .filter(Boolean) as Array<string>;

    return error || undefined;
  };

  const normalizedValues = selectedValues.map((value) => ({
    id: value,
    name: value,
  }));

  const inputErrors = getError();

  return useMemoComponent({
    Component: (
      <div>
        <MultiAutocompleteField
          clearOnBlur
          freeSolo
          handleHomeEndKeys
          isOptionEqualToValue={(option, selectedValue): boolean =>
            equals(option, selectedValue)
          }
          label={`${t(label)} (${t(labelPressEnterToAccept)})`}
          open={false}
          options={[]}
          popupIcon={null}
          required={required}
          value={normalizedValues}
          onChange={change}
        />
        {inputErrors && (
          <Stack>
            {inputErrors.map((error) => (
              <FormHelperText error key={error}>
                {error}
              </FormHelperText>
            ))}
          </Stack>
        )}
      </div>
    ),
    memoProps: [normalizedValues, inputErrors],
  });
};

export default Multiple;
