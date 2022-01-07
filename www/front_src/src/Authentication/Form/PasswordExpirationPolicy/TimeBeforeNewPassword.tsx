import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';

import { FormHelperText, FormLabel } from '@mui/material';

import { useMemoComponent } from '@centreon/centreon-frontend/packages/centreon-ui/src';

import { labelTimeBeforeSettingNewPassword } from '../../translatedLabels';
import { getField } from '../utils';
import TimeInputs from '../../TimeInputs';
import { TimeInputConfiguration } from '../../models';

const delayBeforeNewPasswordFieldName = 'delayBeforeNewPassword';

const timeInputConfigurations: Array<TimeInputConfiguration> = [
  { maxValue: 7, unit: 'days' },
  { unit: 'hours' },
];

const TimeBeforeNewPassword = (): JSX.Element => {
  const { t } = useTranslation();

  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();

  const change = (value: number): void => {
    setFieldValue(delayBeforeNewPasswordFieldName, value || null);
  };

  const delayBeforeNewPasswordValue = getField<number>({
    field: delayBeforeNewPasswordFieldName,
    object: values,
  });

  const delayBeforeNewPasswordError = getField<string>({
    field: delayBeforeNewPasswordFieldName,
    object: errors,
  });

  return useMemoComponent({
    Component: (
      <div>
        <FormLabel>{t(labelTimeBeforeSettingNewPassword)}</FormLabel>
        <TimeInputs
          baseName={delayBeforeNewPasswordFieldName}
          inputLabel={labelTimeBeforeSettingNewPassword}
          timeInputConfigurations={timeInputConfigurations}
          timeValue={delayBeforeNewPasswordValue}
          onChange={change}
        />
        {delayBeforeNewPasswordError && (
          <FormHelperText error>{delayBeforeNewPasswordError}</FormHelperText>
        )}
      </div>
    ),
    memoProps: [delayBeforeNewPasswordValue, delayBeforeNewPasswordError],
  });
};

export default TimeBeforeNewPassword;
