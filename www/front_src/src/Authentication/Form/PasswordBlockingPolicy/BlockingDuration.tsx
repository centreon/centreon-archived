import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';
import { isNil, not } from 'ramda';

import { FormHelperText, FormLabel, useTheme } from '@material-ui/core';

import { useMemoComponent } from '@centreon/centreon-frontend/packages/centreon-ui/src';

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

  return useMemoComponent({
    Component: (
      <div>
        <FormLabel>{t(labelBlockingTimeBeforeNewConnectionAttempt)}</FormLabel>
        <TimeInputs
          baseName={blockingDurationFieldName}
          inputLabel={labelBlockingTimeBeforeNewConnectionAttempt}
          timeValue={blockingDurationValue}
          units={['days', 'hours', 'minutes']}
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
