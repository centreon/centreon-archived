import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  FormControlLabel,
  FormGroup,
  FormLabel,
  RadioGroup,
  Radio as MUIRadio,
} from '@mui/material';

import { useMemoComponent } from '@centreon/ui';

import { InputProps } from '../models';

const Radio = ({
  fieldName,
  getDisabled,
  label,
  options,
}: InputProps): JSX.Element => {
  const { t } = useTranslation();

  const { values, setFieldValue } = useFormikContext<FormikValues>();

  const change = (event: React.ChangeEvent<HTMLInputElement>): void => {
    setFieldValue(fieldName, event.target.value);
  };
  const value = prop(fieldName, values);
  const disabled = getDisabled?.(values);

  return useMemoComponent({
    Component: (
      <FormGroup>
        <FormLabel>{t(label)}</FormLabel>
        <RadioGroup value={value} onChange={change}>
          {options?.map(({ value: optionValue, label: optionLabel }) => (
            <FormControlLabel
              control={<MUIRadio />}
              disabled={getDisabled?.(values)}
              key={optionLabel}
              label={t(optionLabel) as string}
              value={optionValue}
            />
          ))}
        </RadioGroup>
      </FormGroup>
    ),
    memoProps: [value, disabled],
  });
};

export default Radio;
