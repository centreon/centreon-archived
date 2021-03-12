import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { map, pick } from 'ramda';

import { Paper, makeStyles, ButtonGroup, Button } from '@material-ui/core';

import { TimePeriodId, timePeriods } from '../../../Details/tabs/Graph/models';

const useStyles = makeStyles((theme) => ({
  header: {
    padding: theme.spacing(2),
  },
}));

interface Props {
  selectedTimePeriodId: string;
  onChange: (timePeriod: TimePeriodId) => void;
  disabled?: boolean;
}

const timePeriodOptions = map(pick(['id', 'name']), timePeriods);

const TimePeriodButtonGroup = ({
  selectedTimePeriodId,
  onChange,
  disabled = false,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const translatedTimePeriodOptions = timePeriodOptions.map((timePeriod) => ({
    ...timePeriod,
    name: t(timePeriod.name),
  }));

  return (
    <Paper className={classes.header}>
      <ButtonGroup fullWidth size="small" disabled={disabled} color="primary">
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
    </Paper>
  );
};

export default TimePeriodButtonGroup;
