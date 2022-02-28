import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { equals, isNil, map, pluck, prop, type } from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@mui/styles';
import { FormHelperText, Stack } from '@mui/material';

import {
  MultiAutocompleteField,
  SelectEntry,
  useMemoComponent,
} from '@centreon/ui';

import { InputProps } from '../models';

const useStyles = makeStyles({
  errorStack: {
    display: 'flex',
    flexDirection: 'column',
  },
});

const MultiText = ({
  fieldName,
  label,
  getDisabled,
}: InputProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

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

        return `${selectedValues.at(index)}: ${errorText}`;
      })
      .filter(Boolean) as Array<string>;

    return error || undefined;
  };

  const normalizedValues = selectedValues.map((value) => ({
    id: value,
    name: value,
  }));

  const inputErrors = getError();
  const disabled = getDisabled?.(values);

  return useMemoComponent({
    Component: (
      <div>
        <MultiAutocompleteField
          clearOnBlur
          freeSolo
          handleHomeEndKeys
          disabled={getDisabled?.(values)}
          isOptionEqualToValue={(option, selectedValue): boolean =>
            equals(option, selectedValue)
          }
          label={t(label)}
          open={false}
          options={[]}
          popupIcon={null}
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
    memoProps: [normalizedValues, inputErrors, disabled],
  });
};

export default MultiText;
