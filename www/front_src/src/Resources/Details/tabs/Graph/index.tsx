import * as React from 'react';

import { pick, map } from 'ramda';

import { Paper, Theme, makeStyles } from '@material-ui/core';

import { SelectField } from '@centreon/ui';

import {
  timePeriods,
  getTimePeriodById,
  last24hPeriod,
  TimePeriod,
} from './models';
import PerformanceGraph from '../../../Graph/Performance';
import { TabProps } from '..';

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
    padding: theme.spacing(2, 1, 1),
    gridTemplateRows: '1fr',
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
    width: '100%',
  },
}));

const timePeriodSelectOptions = map(pick(['id', 'name']), timePeriods);

const defaultTimePeriod = last24hPeriod;

const GraphTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();

  const { endpoints } = details.links;
  const { performanceGraph: performanceGraphEndpoint } = endpoints;

  const [selectedTimePeriod, setSelectedTimePeriod] = React.useState<
    TimePeriod
  >(defaultTimePeriod);

  const getQueryParams = (timePeriod): string => {
    const now = new Date(Date.now()).toISOString();
    const start = timePeriod.getStart().toISOString();

    return `?start=${start}&end=${now}`;
  };

  const [periodQueryParams, setPeriodQueryParams] = React.useState(
    getQueryParams(selectedTimePeriod),
  );

  const changeSelectedPeriod = (event): void => {
    const timePeriodId = event.target.value;
    const timePeriod = getTimePeriodById(timePeriodId);

    setSelectedTimePeriod(timePeriod);

    const queryParamsForSelectedPeriodId = getQueryParams(timePeriod);
    setPeriodQueryParams(queryParamsForSelectedPeriodId);
  };

  return (
    <div className={classes.container}>
      <Paper className={classes.header}>
        <SelectField
          className={classes.periodSelect}
          options={timePeriodSelectOptions}
          selectedOptionId={selectedTimePeriod.id}
          onChange={changeSelectedPeriod}
        />
      </Paper>
      <Paper className={classes.graphContainer}>
        <div className={`${classes.graph} ${classes.performance}`}>
          <PerformanceGraph
            endpoint={`${performanceGraphEndpoint}${periodQueryParams}`}
            graphHeight={280}
            xAxisTickFormat={selectedTimePeriod.timeFormat}
            toggableLegend
          />
        </div>
      </Paper>
    </div>
  );
};

export default GraphTab;
