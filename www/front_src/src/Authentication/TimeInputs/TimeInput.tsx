import * as React from 'react';

import { useTranslation } from 'react-i18next';
import dayjs from 'dayjs';
import { and, equals, gt, path, subtract } from 'ramda';

import { Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { TextField, useMemoComponent } from '@centreon/ui';

import { Unit } from '../models';

const weeksUnit = 'weeks';

const normalizeValue = ({ unit, value, functionGetDurationValue }): number =>
  equals(unit, weeksUnit)
    ? dayjs.duration(value)[functionGetDurationValue]('days') / 7
    : dayjs.duration(value)[functionGetDurationValue](unit);

interface Labels {
  plural: string;
  singular: string;
}

export interface TimeInputProps {
  getAbsoluteValue?: boolean;
  inputLabel: string;
  labels: Labels;
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
}: TimeInputProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const functionGetDurationValue = getAbsoluteValue ? 'as' : 'get';

  const changeInput = React.useCallback(
    (event: React.ChangeEvent<HTMLInputElement>): void => {
      const value = parseInt(path(['target', 'value'], event) as string, 10);

      const currentDuration = dayjs.duration(timeValue || 0);

      const previousValue = Math.floor(
        currentDuration[functionGetDurationValue](unit),
      );

      if (Number.isNaN(value)) {
        onChange(
          currentDuration
            .clone()
            .subtract(previousValue, unit)
            .as('milliseconds'),
        );

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
        onChange(
          currentDuration
            .clone()
            .subtract(previousValue, 'months')
            .add(1, 'years')
            .asMilliseconds(),
        );

        return;
      }
      onChange(
        currentDuration.clone().add(diffDuration, unit).asMilliseconds(),
      );
    },
    [functionGetDurationValue, unit, timeValue],
  );

  const normalizedValue = React.useMemo(
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
        <TextField
          inputProps={{
            'aria-label': `${t(inputLabel)} ${t(label)}`,
            className: classes.small,
            min: 0,
          }}
          name={name}
          required={required}
          type="number"
          value={equals(inputValue, 0) ? '' : inputValue}
          onChange={changeInput}
        />
        <Typography>{t(label)}</Typography>
      </div>
    ),
    memoProps: [timeValue, unit, labels, name, required, getAbsoluteValue],
  });
};

export default TimeInput;
