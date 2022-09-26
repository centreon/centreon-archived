import { useMemo } from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import dayjs from 'dayjs';
import { lte } from 'ramda';

import { FormHelperText, FormLabel } from '@mui/material';

import { useMemoComponent } from '@centreon/ui';

import { labelMinimumTimeBetweenPasswordChanges } from '../translatedLabels';
import TimeInputs from '../TimeInputs';
import { TimeInputConfiguration } from '../models';
import { sevenDays } from '../timestamps';

import { getField } from './utils';

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

  const maxHoursOption = useMemo(
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
    {
      dataTestId: 'local_timeBetweenPasswordChangesDays',
      maxOption: 7,
      unit: 'days',
    },
    {
      dataTestId: 'local_timeBetweenPasswordChangesHours',
      maxOption: maxHoursOption,
      unit: 'hours',
    },
  ];

  return useMemoComponent({
    Component: (
      <div>
        <FormLabel>{t(labelMinimumTimeBetweenPasswordChanges)}</FormLabel>
        <TimeInputs
          baseName={delayBeforeNewPasswordFieldName}
          inputLabel={labelMinimumTimeBetweenPasswordChanges}
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
