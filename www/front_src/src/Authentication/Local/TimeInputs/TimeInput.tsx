import { useCallback, useMemo } from 'react';

import { useTranslation } from 'react-i18next';
import dayjs from 'dayjs';
import { and, equals, gt, path, subtract } from 'ramda';

import { SelectChangeEvent, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { SelectField, useMemoComponent } from '@centreon/ui';

import { Unit } from '../models';

import {
  getDaysOptions,
  getHoursOptions,
  getMinutesOptions,
  getMonthsOptions,
} from './options';

const weeksUnit = 'weeks';

const normalizeValue = ({ unit, value, functionGetDurationValue }): number =>
  equals(unit, weeksUnit)
    ? dayjs.duration(value)[functionGetDurationValue]('days') / 7
    : dayjs.duration(value)[functionGetDurationValue](unit);

const getTimeOptions = {
  days: getDaysOptions,
  hours: getHoursOptions,
  minutes: getMinutesOptions,
  months: getMonthsOptions,
};

interface Labels {
  plural: string;
  singular: string;
}

export interface TimeInputProps {
  getAbsoluteValue?: boolean;
  inputLabel: string;
  labels: Labels;
  maxDuration?: number;
  maxOption?: number;
  minDuration?: number;
  minOption?: number;
  name: string;
  onChange: (value: number) => void;
  required?: boolean;
  timeValue: number;
  unit: Unit;
}

const useStyles = makeStyles((theme) => ({
  small: {
    fontSize: 'small',
    padding: theme.spacing(0.75),
  },
  timeInput: {
    alignItems: 'center',
    columnGap: theme.spacing(0.5),
    display: 'grid',
    gridTemplateColumns: `${theme.spacing(8)} auto`,
  },
}));

const TimeInput = ({
  timeValue,
  unit,
  onChange,
  labels,
  name,
  required = false,
  getAbsoluteValue = false,
  inputLabel,
  maxOption,
  minOption,
  maxDuration,
}: TimeInputProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const functionGetDurationValue = getAbsoluteValue ? 'as' : 'get';

  const normalizeDuration = (duration: number): number => {
    if (maxDuration && gt(duration, maxDuration)) {
      return maxDuration;
    }

    return duration;
  };

  const changeInput = useCallback(
    (event: SelectChangeEvent<unknown>): void => {
      const value = parseInt(path(['target', 'value'], event) as string, 10);

      const currentDuration = dayjs.duration(timeValue || 0);

      const previousValue = Math.floor(
        currentDuration[functionGetDurationValue](unit),
      );

      if (Number.isNaN(value)) {
        const newDuration = currentDuration
          .clone()
          .subtract(previousValue, unit)
          .as('milliseconds');

        onChange(normalizeDuration(newDuration));

        return;
      }

      const diffDuration = subtract(value, previousValue);
      if (
        and(
          equals(unit, 'months'),
          equals(
            currentDuration.clone().add(diffDuration, unit).asMonths(),
            12,
          ),
        )
      ) {
        const newDuration = currentDuration
          .clone()
          .subtract(previousValue, 'months')
          .add(1, 'years')
          .asMilliseconds();
        onChange(normalizeDuration(newDuration));

        return;
      }

      const newDuration = currentDuration
        .clone()
        .add(diffDuration, unit)
        .asMilliseconds();
      onChange(normalizeDuration(newDuration));
    },
    [functionGetDurationValue, unit, timeValue],
  );

  const normalizedValue = useMemo(
    () =>
      normalizeValue({
        functionGetDurationValue,
        unit,
        value: timeValue || 0,
      }),
    [functionGetDurationValue, unit, timeValue],
  );
  const inputValue = Math.floor(normalizedValue);

  const label = gt(inputValue, 1) ? labels.plural : labels.singular;

  return useMemoComponent({
    Component: (
      <div className={classes.timeInput}>
        <SelectField
          inputProps={{
            'aria-label': `${t(inputLabel)} ${t(label)}`,
          }}
          name={name}
          options={getTimeOptions[unit]({ max: maxOption, min: minOption })}
          required={required}
          selectedOptionId={inputValue}
          onChange={changeInput}
        />
        <Typography>{t(label)}</Typography>
      </div>
    ),
    memoProps: [timeValue, unit, labels, name, required, getAbsoluteValue],
  });
};

export default TimeInput;
