import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { map, pick } from 'ramda';

import { Paper, makeStyles, ButtonGroup, Button } from '@material-ui/core';

import {
  ChangeCustomTimePeriodProps,
  CustomTimePeriod,
  TimePeriodId,
  timePeriods,
} from '../../../Details/tabs/Graph/models';

import CustomTimePeriodPickers from './CustomTimePeriodPickers';

const useStyles = makeStyles((theme) => ({
  header: {
    padding: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: `repeat(2, auto)`,
    columnGap: `${theme.spacing(2)}px`,
    justifyContent: 'center',
  },
  buttonGroup: {
    alignSelf: 'center',
  },
  button: {
    padding: theme.spacing(0.8, 0.5),
    fontSize: '0.75rem',
    lineHeight: 1.2,
  },
}));

interface Props {
  selectedTimePeriodId?: string;
  onChange: (timePeriod: TimePeriodId) => void;
  disabled?: boolean;
  customTimePeriod: CustomTimePeriod;
  changeCustomTimePeriod: (props: ChangeCustomTimePeriodProps) => void;
}

const timePeriodOptions = map(pick(['id', 'name']), timePeriods);

const TimePeriodButtonGroup = ({
  selectedTimePeriodId,
  onChange,
  disabled = false,
  customTimePeriod,
  changeCustomTimePeriod,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const translatedTimePeriodOptions = timePeriodOptions.map((timePeriod) => ({
    ...timePeriod,
    name: t(timePeriod.name),
  }));

  const changeDate = ({ property, date }) =>
    changeCustomTimePeriod({ date, property });

  return (
    <Paper className={classes.header}>
      <ButtonGroup
        size="small"
        disabled={disabled}
        color="primary"
        className={classes.buttonGroup}
      >
        {map(
          ({ id, name }) => (
            <Button
              key={name}
              onClick={() => onChange(id)}
              variant={selectedTimePeriodId === id ? 'contained' : 'outlined'}
              className={classes.button}
            >
              {name}
            </Button>
          ),
          translatedTimePeriodOptions,
        )}
      </ButtonGroup>
      <CustomTimePeriodPickers
        customTimePeriod={customTimePeriod}
        acceptDate={changeDate}
      />
    </Paper>
  );
};

export default TimePeriodButtonGroup;
