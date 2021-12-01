import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';
import { and, isNil, not } from 'ramda';

import { FormHelperText, FormLabel, useTheme } from '@material-ui/core';

import TimeInputs from '../../TimeInputs';
import {
  labelBlockingTimeBeforeNewConnectionAttempt,
  labelGood,
  labelStrong,
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

const blockingDurationFieldName = 'blockingDuration';

const BlockingDuration = (): JSX.Element => {
  const { t } = useTranslation();
  const theme = useTheme();

  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();

  const change = (value: number): void => {
    setFieldValue(blockingDurationFieldName, value || null);
  };

  const blockingDurationValue = React.useMemo<number>(
    () => getField({ field: blockingDurationFieldName, object: values }),
    [values],
  );

  const blockingDurationError = React.useMemo<number>(
    () => getField({ field: blockingDurationFieldName, object: errors }),
    [errors],
  );

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
    [theme],
  );

  const displayStrengthProgress = and(
    isNil(blockingDurationError),
    not(isNil(blockingDurationValue)),
  );

  return (
    <div>
      <FormLabel>{t(labelBlockingTimeBeforeNewConnectionAttempt)}</FormLabel>
      <TimeInputs
        baseName={blockingDurationFieldName}
        timeValue={blockingDurationValue}
        units={['days', 'hours', 'minutes', 'seconds']}
        onChange={change}
      />
      {blockingDurationError && (
        <FormHelperText error>{blockingDurationError}</FormHelperText>
      )}
      {displayStrengthProgress && (
        <StrengthProgress
          max={sevenDays}
          thresholds={thresholds}
          value={blockingDurationValue || 0}
        />
      )}
    </div>
  );
};

export default BlockingDuration;
