import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';
import { isNil, lte, not } from 'ramda';
import dayjs from 'dayjs';

import { FormHelperText, FormLabel, useTheme } from '@mui/material';

import { useMemoComponent } from '@centreon/ui';

import TimeInputs from '../../TimeInputs';
import {
  labelBlockingTimeBeforeNewConnectionAttempt,
  labelGood,
  labelStrong,
  labelThisWillNotBeUsedBecauseNumberOfAttemptsIsNotDefined,
  labelWeak,
} from '../../translatedLabels';
import { getField } from '../utils';
import StrengthProgress from '../../StrengthProgress';
import {
  goodBlockingDuration,
  sevenDays,
  strongBlockingDuration,
  weakBlockingDuration,
} from '../../timestamps';
import { TimeInputConfiguration } from '../../models';

import { attemptsFieldName } from './Attempts';

const blockingDurationFieldName = 'blockingDuration';

const BlockingDuration = (): JSX.Element => {
  const { t } = useTranslation();
  const theme = useTheme();

  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();

  const change = (value: number): void => {
    setFieldValue(blockingDurationFieldName, value || null);
  };

  const blockingDurationValue = getField<number>({
    field: blockingDurationFieldName,
    object: values,
  });

  const blockingDurationError = getField<string>({
    field: blockingDurationFieldName,
    object: errors,
  });

  const attemptsValue = getField<number>({
    field: attemptsFieldName,
    object: values,
  });

  const thresholds = React.useMemo(
    () => [
      {
        color: theme.palette.error.main,
        label: labelWeak,
        value: weakBlockingDuration,
      },
      {
        color: theme.palette.warning.main,
        label: labelGood,
        value: goodBlockingDuration,
      },
      {
        color: theme.palette.success.main,
        label: labelStrong,
        value: strongBlockingDuration,
      },
    ],
    [],
  );

  const areAttemptsEmpty = isNil(attemptsValue);

  const displayStrengthProgress = React.useMemo(
    () =>
      isNil(blockingDurationError) &&
      not(isNil(blockingDurationValue)) &&
      not(areAttemptsEmpty),
    [blockingDurationError, blockingDurationValue, areAttemptsEmpty],
  );

  const maxHoursAndMinutesOption = React.useMemo(
    (): number | undefined =>
      lte(dayjs.duration({ days: 7 }).asMilliseconds(), blockingDurationValue)
        ? 0
        : undefined,
    [blockingDurationValue],
  );

  const timeInputConfigurations: Array<TimeInputConfiguration> = [
    { maxOption: 7, unit: 'days' },
    { maxOption: maxHoursAndMinutesOption, unit: 'hours' },
    { maxOption: maxHoursAndMinutesOption, unit: 'minutes' },
  ];

  return useMemoComponent({
    Component: (
      <div>
        <FormLabel>{t(labelBlockingTimeBeforeNewConnectionAttempt)}</FormLabel>
        <TimeInputs
          baseName={blockingDurationFieldName}
          inputLabel={labelBlockingTimeBeforeNewConnectionAttempt}
          timeInputConfigurations={timeInputConfigurations}
          timeValue={blockingDurationValue}
          onChange={change}
        />
        {blockingDurationError && (
          <FormHelperText error>{blockingDurationError}</FormHelperText>
        )}
        {areAttemptsEmpty && (
          <FormHelperText error>
            {t(labelThisWillNotBeUsedBecauseNumberOfAttemptsIsNotDefined)}
          </FormHelperText>
        )}
        {displayStrengthProgress && (
          <StrengthProgress
            max={sevenDays}
            thresholds={thresholds}
            value={blockingDurationValue || 0}
          />
        )}
      </div>
    ),
    memoProps: [blockingDurationValue, blockingDurationError, attemptsValue],
  });
};

export default BlockingDuration;
