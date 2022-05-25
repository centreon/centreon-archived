import { useState } from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { equals, isNil, map, prop, type } from 'ramda';
import { useTranslation } from 'react-i18next';

import { FormHelperText, Stack } from '@mui/material';

import {
  MultiAutocompleteField,
  SelectEntry,
  useMemoComponent,
} from '@centreon/ui';

import { labelPressEnterToAccept } from '../translatedLabels';

import { InputPropsWithoutCategory } from './models';

const Multiple = ({
  fieldName,
  label,
  required,
  getDisabled,
  getRequired,
  change,
}: InputPropsWithoutCategory): JSX.Element => {
  const { t } = useTranslation();

  const [inputText, setInputText] = useState('');

  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();

  const changeMultiple = (_, newValues): void => {
    const normalizedNewValues = map((newValue: SelectEntry | string) => {
      if (equals(type(newValue), 'String')) {
        return newValue;
      }

      return prop('name', newValue as SelectEntry);
    }, newValues);

    if (change) {
      change({ setFieldValue, value: normalizedNewValues });

      return;
    }

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

  const textChange = (event): void => setInputText(event.target.value);

  const normalizedValues = selectedValues.map((value) => ({
    id: value,
    name: value,
  }));

  const inputErrors = getError();

  const disabled = getDisabled?.(values) || false;
  const isRequired = required || getRequired?.(values) || false;
  const additionalLabel = inputText ? ` (${labelPressEnterToAccept})` : '';

  return useMemoComponent({
    Component: (
      <div>
        <MultiAutocompleteField
          freeSolo
          disabled={disabled}
          inputValue={inputText}
          isOptionEqualToValue={(option, selectedValue): boolean =>
            equals(option, selectedValue)
          }
          label={`${t(label)}${additionalLabel}`}
          open={false}
          options={[]}
          popupIcon={null}
          required={isRequired}
          value={normalizedValues}
          onChange={changeMultiple}
          onTextChange={textChange}
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
    memoProps: [normalizedValues, inputErrors, additionalLabel, disabled],
  });
};

export default Multiple;
