import * as React from 'react';

import { useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';
import { and, isNil, not } from 'ramda';

import { FormHelperText, FormLabel, useTheme } from '@material-ui/core';

import { SecurityPolicy } from '../../models';
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
  goodBlockingDurationInMilliseconds,
  sevenDaysInMilliseconds,
  strongBlockingDurationInMilliseconds,
  weakBlockingDurationInMilliseconds,
} from '../../timestamps';

const blockingDurationFieldName = 'blockingDuration';

const BlockingDuration = (): JSX.Element => {
  const { t } = useTranslation();
  const theme = useTheme();

  const { values, setFieldValue, errors } = useFormikContext<SecurityPolicy>();

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
        value: weakBlockingDurationInMilliseconds,
      },
      {
        color: theme.palette.warning.main,
        label: labelGood,
        value: goodBlockingDurationInMilliseconds,
      },
      {
        color: theme.palette.success.main,
        label: labelStrong,
        value: strongBlockingDurationInMilliseconds,
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
          max={sevenDaysInMilliseconds}
          thresholds={thresholds}
          value={blockingDurationValue || 0}
        />
      )}
    </div>
  );
};

export default BlockingDuration;
