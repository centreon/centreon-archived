import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import dayjs from 'dayjs';
import { lte } from 'ramda';

import { FormHelperText, FormLabel } from '@mui/material';

import { useMemoComponent } from '@centreon/ui';

import { labelTimeBeforeSettingNewPassword } from '../../translatedLabels';
import { getField } from '../utils';
import TimeInputs from '../../TimeInputs';
import { TimeInputConfiguration } from '../../models';
import { sevenDays } from '../../timestamps';

const delayBeforeNewPasswordFieldName = 'delayBeforeNewPassword';

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

  const maxHoursOption = React.useMemo(
    (): number | undefined =>
      lte(
        dayjs.duration({ days: 7 }).asMilliseconds(),
        delayBeforeNewPasswordValue,
      )
        ? 0
        : undefined,
    [delayBeforeNewPasswordValue],
  );

  const timeInputConfigurations: Array<TimeInputConfiguration> = [
    { maxOption: 7, unit: 'days' },
    { maxOption: maxHoursOption, unit: 'hours' },
  ];

  return useMemoComponent({
    Component: (
      <div>
        <FormLabel>{t(labelTimeBeforeSettingNewPassword)}</FormLabel>
        <TimeInputs
          baseName={delayBeforeNewPasswordFieldName}
          inputLabel={labelTimeBeforeSettingNewPassword}
          maxDuration={sevenDays}
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
