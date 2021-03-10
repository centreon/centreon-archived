import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { map, pick } from 'ramda';

import { Paper, makeStyles, ButtonGroup, Button } from '@material-ui/core';

import {
  ChangeTimeframeProps,
  Timeframe,
  TimePeriodId,
  timePeriods,
} from '../../../Details/tabs/Graph/models';

import CustomTimeframePickers from './CustomTimeframePickers';

const useStyles = makeStyles((theme) => ({
  header: {
    padding: theme.spacing(2),
    display: 'grid',
    gridTemplateColumns: `repeat(auto-fit, minmax(${theme.spacing(
      25,
    )}px, ${theme.spacing(43)}px))`,
    columnGap: `${theme.spacing(1)}px`,
    rowGap: `${theme.spacing(1)}px`,
    justifyItems: 'center',
  },
  buttonGroup: {
    alignSelf: 'center',
  },
}));

interface Props {
  selectedTimePeriodId?: string;
  onChange: (timePeriod: TimePeriodId) => void;
  disabled?: boolean;
  timeframe: Timeframe;
  changeTimeframe: (props: ChangeTimeframeProps) => void;
}

const timePeriodOptions = map(pick(['id', 'name']), timePeriods);

const TimePeriodButtonGroup = ({
  selectedTimePeriodId,
  onChange,
  disabled = false,
  timeframe,
  changeTimeframe,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const translatedTimePeriodOptions = timePeriodOptions.map((timePeriod) => ({
    ...timePeriod,
    name: t(timePeriod.name),
  }));

  const changeDate = ({ property, date }) =>
    changeTimeframe({ date, property });

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
            >
              {name}
            </Button>
          ),
          translatedTimePeriodOptions,
        )}
      </ButtonGroup>
      <CustomTimeframePickers timeframe={timeframe} acceptDate={changeDate} />
    </Paper>
  );
};

export default TimePeriodButtonGroup;
