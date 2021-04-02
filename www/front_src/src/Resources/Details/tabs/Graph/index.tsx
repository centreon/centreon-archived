import * as React from 'react';

import { path } from 'ramda';

import { Theme, makeStyles } from '@material-ui/core';

import { TabProps } from '..';
import useTimePeriod from '../../../Graph/Performance/TimePeriods/useTimePeriod';
import TimePeriodButtonGroup from '../../../Graph/Performance/TimePeriods';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import { ResourceContext, useResourceContext } from '../../../Context';
import memoizeComponent from '../../../memoizedComponent';
import { GraphOptions } from '../../models';
import useGraphOptions, {
  GraphOptionsContext,
} from '../../../Graph/Performance/ExportableGraphWithTimeline/useGraphOptions';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridTemplateRows: 'auto 1fr',
    gridRowGap: theme.spacing(2),
  },
  exportToPngButton: {
    display: 'flex',
    justifyContent: 'space-between',
    margin: theme.spacing(0, 1, 1, 2),
  },
  graphContainer: {
    display: 'grid',
    padding: theme.spacing(2, 1, 1),
    gridTemplateRows: '1fr',
  },
  graph: {
    margin: 'auto',
    height: '100%',
    width: '100%',
  },
}));

type GraphTabContentProps = TabProps &
  Pick<ResourceContext, 'tabParameters' | 'setGraphTabParameters'>;

const GraphTabContent = ({
  details,
  tabParameters,
  setGraphTabParameters,
}: GraphTabContentProps): JSX.Element => {
  const classes = useStyles();

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
    getIntervalDates,
    customTimePeriod,
    changeCustomTimePeriod,
    adjustTimePeriod,
    resourceDetailsUpdated,
  } = useTimePeriod({
    defaultSelectedTimePeriodId: path(
      ['graph', 'selectedTimePeriodId'],
      tabParameters,
    ),
    defaultSelectedCustomTimePeriod: path(
      ['graph', 'selectedCustomTimePeriod'],
      tabParameters,
    ),
    defaultGraphOptions: path(['graph', 'graphOptions'], tabParameters),
    details,
    onTimePeriodChange: setGraphTabParameters,
  });

  const changeTabGraphOptions = (graphOptions: GraphOptions) => {
    setGraphTabParameters({
      ...tabParameters.graph,
      graphOptions,
    });
  };

  const graphOptions = useGraphOptions({
    graphTabParameters: tabParameters.graph,
    changeTabGraphOptions,
  });

  return (
    <GraphOptionsContext.Provider value={graphOptions}>
      <div className={classes.container}>
        <TimePeriodButtonGroup
          selectedTimePeriodId={selectedTimePeriod?.id}
          onChange={changeSelectedTimePeriod}
          customTimePeriod={customTimePeriod}
          changeCustomTimePeriod={changeCustomTimePeriod}
        />
        <ExportablePerformanceGraphWithTimeline
          resource={details}
          graphHeight={280}
          periodQueryParameters={periodQueryParameters}
          getIntervalDates={getIntervalDates}
          selectedTimePeriod={selectedTimePeriod}
          customTimePeriod={customTimePeriod}
          adjustTimePeriod={adjustTimePeriod}
          resourceDetailsUpdated={resourceDetailsUpdated}
        />
      </div>
    </GraphOptionsContext.Provider>
  );
};

const MemoizedGraphTabContent = memoizeComponent<GraphTabContentProps>({
  memoProps: ['details', 'tabParameters'],
  Component: GraphTabContent,
});

const GraphTab = ({ details }: TabProps): JSX.Element => {
  const { tabParameters, setGraphTabParameters } = useResourceContext();

  return (
    <MemoizedGraphTabContent
      details={details}
      tabParameters={tabParameters}
      setGraphTabParameters={setGraphTabParameters}
    />
  );
};

export default GraphTab;
