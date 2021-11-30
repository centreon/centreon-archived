import * as React from 'react';

import { equals, find, propEq } from 'ramda';

import { makeStyles } from '@material-ui/core';

import { labelSeconds } from '../../Resources/translatedLabels';
import {
  labelDay,
  labelDays,
  labelHour,
  labelHours,
  labelMinute,
  labelMinutes,
  labelMonth,
  labelMonths,
  labelSecond,
} from '../translatedLabels';
import { Unit } from '../models';

import TimeInput from './TimeInput';

const useStyles = makeStyles((theme) => ({
  timeInputs: {
    columnGap: theme.spacing(1.5),
    display: 'flex',
    flexDirection: 'row',
    marginBottom: theme.spacing(0.5),
    marginTop: theme.spacing(0.5),
  },
}));

interface UnitAndLabels {
  pluralLabel: string;
  singularLabel: string;
  unit: string;
}

const mapUnitAndLabels: Array<UnitAndLabels> = [
  { pluralLabel: labelSeconds, singularLabel: labelSecond, unit: 'seconds' },
  { pluralLabel: labelMinutes, singularLabel: labelMinute, unit: 'minutes' },
  { pluralLabel: labelHours, singularLabel: labelHour, unit: 'hours' },
  { pluralLabel: labelDays, singularLabel: labelDay, unit: 'days' },
  { pluralLabel: labelMonths, singularLabel: labelMonth, unit: 'months' },
];

interface Props {
  baseName: string;
  onChange: (value: number) => void;
  timeValue: number;
  units: Array<Unit>;
}

const TimeInputs = ({
  baseName,
  units,
  onChange,
  timeValue,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.timeInputs}>
      {units.map((unit, idx) => {
        const { pluralLabel, singularLabel } = find(
          propEq('unit', unit),
          mapUnitAndLabels,
        ) as UnitAndLabels;

        return (
          <TimeInput
            getAbsoluteValue={equals(idx, 0)}
            key={singularLabel}
            labels={{
              plural: pluralLabel,
              singular: singularLabel,
            }}
            name={`${baseName}_${singularLabel}`}
            timeValue={timeValue}
            unit={unit}
            onChange={onChange}
          />
        );
      })}
    </div>
  );
};

export default TimeInputs;
