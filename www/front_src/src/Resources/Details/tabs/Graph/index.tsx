import * as React from 'react';

import { pick, map, path, isNil } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Paper, Theme, makeStyles } from '@material-ui/core';

import { SelectField } from '@centreon/ui';

import PerformanceGraph from '../../../Graph/Performance';
import { TabProps } from '..';

import {
  timePeriods,
  getTimePeriodById,
  last24hPeriod,
  TimePeriod,
} from './models';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridRowGap: theme.spacing(2),
    gridTemplateRows: 'auto 1fr',
  },
  graph: {
    height: '100%',
    margin: 'auto',
  },
  graphContainer: {
    display: 'grid',
    gridTemplateRows: '1fr',
    padding: theme.spacing(2, 1, 1),
  },
  header: {
    padding: theme.spacing(2),
  },
  performance: {
    width: '100%',
  },
  periodSelect: {
    width: 250,
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
  const { t } = useTranslation();

  const translatedTimePeriodSelectOptions = timePeriodSelectOptions.map(
    (timePeriod) => ({
      ...timePeriod,
      name: t(timePeriod.name),
    }),
  );

  const endpoint = path(['links', 'endpoints', 'performance_graph'], details);

  const [selectedTimePeriod, setSelectedTimePeriod] =
    React.useState<TimePeriod>(defaultTimePeriod);

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

  const getEndpoint = (): string | undefined => {
    if (isNil(endpoint)) {
      return undefined;
    }

    return `${endpoint}${periodQueryParams}`;
  };

  return (
    <div className={classes.container}>
      <Paper className={classes.header}>
        <SelectField
          className={classes.periodSelect}
          options={translatedTimePeriodSelectOptions}
          selectedOptionId={selectedTimePeriod.id}
          onChange={changeSelectedPeriod}
        />
      </Paper>
      <Paper className={classes.graphContainer}>
        <div className={`${classes.graph} ${classes.performance}`}>
          <PerformanceGraph
            toggableLegend
            endpoint={getEndpoint()}
            graphHeight={280}
            xAxisTickFormat={selectedTimePeriod.timeFormat}
          />
        </div>
      </Paper>
    </div>
  );
};

export default GraphTab;
