import * as React from 'react';

import { useTranslation } from 'react-i18next';
import dayjs from 'dayjs';
import { equals, gt, isEmpty, isNil, path } from 'ramda';

import { makeStyles, Typography } from '@material-ui/core';

import { TextField } from '@centreon/ui';

import { Unit } from './models';

const weeksUnit = 'weeks';

const normalizeValue = ({ unit, value }): number =>
  equals(unit, weeksUnit)
    ? dayjs.duration(value).as('days') / 7
    : dayjs.duration(value).as(unit);

interface Labels {
  plural: string;
  singular: string;
}

interface Props {
  labels: Labels;
  name: string;
  onChange: (value: number) => void;
  required?: boolean;
  timeValue: number;
  unit: Unit;
}

const useStyles = makeStyles((theme) => ({
  timeInput: {
    alignItems: 'center',
    columnGap: theme.spacing(1),
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
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const changeInput = (event: React.ChangeEvent<HTMLInputElement>): void => {
    const value = parseInt(path(['target', 'value'], event) as string, 10);

    const duration = dayjs.duration(timeValue);
    if (Number.isNaN(value)) {
      if (equals(unit, weeksUnit)) {
        const previousValue = Math.floor(duration.as('days'));
        onChange(
          duration.clone().subtract(previousValue, 'days').as('milliseconds'),
        );

        return;
      }
      const previousValue = Math.floor(duration.as(unit));
      onChange(
        duration.clone().subtract(previousValue, unit).as('milliseconds'),
      );
    }

    if (equals(unit, weeksUnit)) {
      const previousValue = Math.floor(duration.as('days') / 7);
      const diffDuration = value - previousValue;
      onChange(
        duration
          .clone()
          .add(diffDuration * 7, 'days')
          .as('milliseconds'),
      );

      return;
    }
    const previousValue = Math.floor(duration.as(unit));
    const diffDuration = value - previousValue;
    onChange(duration.clone().add(diffDuration, unit).as('milliseconds'));
  };

  const normalizedValue = normalizeValue({
    unit,
    value: timeValue,
  });
  const inputValue = Math.floor(normalizedValue);

  const label = gt(inputValue, 1) ? labels.plural : labels.singular;

  return (
    <div className={classes.timeInput}>
      <TextField
        inputProps={{ min: 0 }}
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
