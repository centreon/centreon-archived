import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { map, pick } from 'ramda';

import { Paper, makeStyles } from '@material-ui/core';

import { SelectField } from '@centreon/ui';

import { timePeriods } from '../../../Details/tabs/Graph/models';

const useStyles = makeStyles((theme) => ({
  header: {
    padding: theme.spacing(2),
  },
  select: {
    maxWidth: 250,
  },
}));

interface Props {
  selectedTimePeriodId: string;
  onChange: (event) => void;
  disabled?: boolean;
}

const timePeriodSelectOptions = map(pick(['id', 'name']), timePeriods);

const TimePeriodSelect = ({
  selectedTimePeriodId,
  onChange,
  disabled = false,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const translatedTimePeriodSelectOptions = timePeriodSelectOptions.map(
    (timePeriod) => ({
      ...timePeriod,
      name: t(timePeriod.name),
    }),
  );

  return (
    <Paper className={classes.header}>
      <SelectField
        className={classes.select}
        disabled={disabled}
        options={translatedTimePeriodSelectOptions}
        selectedOptionId={selectedTimePeriodId}
        onChange={onChange}
        fullWidth
      />
    </Paper>
  );
};

export default TimePeriodSelect;
