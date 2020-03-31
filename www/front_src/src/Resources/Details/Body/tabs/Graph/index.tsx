import * as React from 'react';

import { pick, map } from 'ramda';
import formatISO from 'date-fns/formatISO';

import { Paper, Theme, makeStyles } from '@material-ui/core';

import { SelectField } from '@centreon/ui';

import PerformanceGraph from '../../../../Graph/Performance';
import StatusGraph from '../../../../Graph/Status';
import { TimePeriodId, timePeriods, getTimePeriodById } from './models';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridTemplateRows: 'auto 1fr',
    gridRowGap: theme.spacing(2),
  },
  header: {
    padding: theme.spacing(2),
  },
  periodSelect: {
    width: 250,
  },
  graphContainer: {
    display: 'grid',
    padding: theme.spacing(4),
    gridTemplateRows: '250px 100px',
  },
  graph: {
    margin: 'auto',
    height: '100%',
  },
  performance: {
    width: '100%',
  },
  status: {
    marginTop: theme.spacing(2),
    width: '90%',
  },
}));

const timePeriodSelectOptions = map(pick(['id', 'name']), timePeriods);

const GraphTab = (): JSX.Element => {
  const classes = useStyles();

  const [selectedTimePeriodId, setSelectedTimePeriodId] = React.useState<
    TimePeriodId
  >('last_24_h');

  const getEndpointParams = (): string => {
    const selectedTimePeriod = getTimePeriodById(selectedTimePeriodId);

    const now = formatISO(new Date());
    const start = formatISO(selectedTimePeriod.getStart());

    return `?start=${start}&end=${now}`;
  };

  const changePeriodId = (event): void => {
    setSelectedTimePeriodId(event.target.value);
  };

  return (
    <div className={classes.container}>
      <Paper className={classes.header}>
        <SelectField
          className={classes.periodSelect}
          options={timePeriodSelectOptions}
          selectedOptionId={selectedTimePeriodId}
          onChange={changePeriodId}
        />
      </Paper>
      <Paper className={classes.graphContainer}>
        <div className={`${classes.graph} ${classes.performance}`}>
          <PerformanceGraph
            endpoint={`http://localhost:5000/api/beta/graph${getEndpointParams()}`}
          />
        </div>
        <div className={`${classes.graph} ${classes.status}`}>
          <StatusGraph
            endpoint={`http://localhost:5000/api/beta/graph${getEndpointParams()}`}
          />
        </div>
      </Paper>
    </div>
  );
};

export default GraphTab;
