import { FormikValues, useFormikContext } from 'formik';
import { equals, includes, prop } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  FormControlLabel,
  FormGroup,
  FormLabel,
  RadioGroup,
  Radio as MUIRadio,
} from '@mui/material';

import { useMemoComponent } from '@centreon/ui';

import { InputProps } from './models';

const Radio = ({
  fieldName,
  label,
  options,
  getDisabled,
}: InputProps): JSX.Element => {
  const { t } = useTranslation();

  const { values, setFieldValue } = useFormikContext<FormikValues>();

  const change = (_, value): void => {
    if (includes(value, ['true', 'false'])) {
      setFieldValue(fieldName, equals(value, 'true'));

      return;
    }
    setFieldValue(fieldName, value);
  };

  const value = prop(fieldName, values);

  const disabled = getDisabled?.(values) || false;

  return useMemoComponent({
    Component: (
      <FormGroup>
        <FormLabel>{t(label)}</FormLabel>
        <RadioGroup value={value} onChange={change}>
          {options?.map(({ value: optionValue, label: optionLabel }) => (
            <FormControlLabel
              control={
                <MUIRadio
                  disabled={disabled}
                  inputProps={{ 'aria-label': t(optionLabel) }}
                />
              }
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
