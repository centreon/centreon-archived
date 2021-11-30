import * as React from 'react';

import { useTranslation } from 'react-i18next';
import dayjs from 'dayjs';
import { equals, gt, path } from 'ramda';

import { makeStyles, Typography } from '@material-ui/core';

import { TextField } from '@centreon/ui';

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

interface Props {
  getAbsoluteValue?: boolean;
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
    gridTemplateColumns: `${theme.spacing(8)}px auto`,
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
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const functionGetDurationValue = getAbsoluteValue ? 'as' : 'get';

  const changeInput = (event: React.ChangeEvent<HTMLInputElement>): void => {
    const value = parseInt(path(['target', 'value'], event) as string, 10);

    const currentDuration = dayjs.duration(timeValue || 0);

    if (Number.isNaN(value)) {
      const previousValue = Math.floor(
        currentDuration[functionGetDurationValue](unit),
      );
      onChange(
        currentDuration
          .clone()
          .subtract(previousValue, unit)
          .as('milliseconds'),
      );

      return;
    }

    const previousValue = Math.floor(
      currentDuration[functionGetDurationValue](unit),
    );
    const diffDuration = value - previousValue;
    if (
      equals(unit, 'months') &&
      equals(currentDuration.clone().add(diffDuration, unit).asMonths(), 12)
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
    onChange(currentDuration.clone().add(diffDuration, unit).asMilliseconds());
  };

  const normalizedValue = normalizeValue({
    functionGetDurationValue,
    unit,
    value: timeValue || 0,
  });
  const inputValue = Math.floor(normalizedValue);

  const label = gt(inputValue, 1) ? labels.plural : labels.singular;

  return (
    <div className={classes.timeInput}>
      <TextField
        inputProps={{
          'aria-label': t(label),
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
  );
};

export default TimeInput;
