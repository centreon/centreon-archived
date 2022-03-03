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
import useMousePosition, {
  MousePositionContext,
} from '../../../Graph/Performance/ExportableGraphWithTimeline/useMousePosition';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridRowGap: theme.spacing(2),
    gridTemplateRows: 'auto 1fr',
  },
  exportToPngButton: {
    display: 'flex',
    justifyContent: 'space-between',
    margin: theme.spacing(0, 1, 1, 2),
  },
  graph: {
    height: '100%',
    margin: 'auto',
    width: '100%',
  },
  graphContainer: {
    display: 'grid',
    gridTemplateRows: '1fr',
    padding: theme.spacing(2, 1, 1),
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
    defaultGraphOptions: path(['graph', 'graphOptions'], tabParameters),
    defaultSelectedCustomTimePeriod: path(
      ['graph', 'selectedCustomTimePeriod'],
      tabParameters,
    ),
    defaultSelectedTimePeriodId: path(
      ['graph', 'selectedTimePeriodId'],
      tabParameters,
    ),
    details,
    onTimePeriodChange: setGraphTabParameters,
  });

  const mousePositionProps = useMousePosition();

  const changeTabGraphOptions = (graphOptions: GraphOptions) => {
    setGraphTabParameters({
      ...tabParameters.graph,
      graphOptions,
    });
  };

  const graphOptions = useGraphOptions({
    changeTabGraphOptions,
    graphTabParameters: tabParameters.graph,
  });

  return (
    <GraphOptionsContext.Provider value={graphOptions}>
      <div className={classes.container}>
        <TimePeriodButtonGroup
          changeCustomTimePeriod={changeCustomTimePeriod}
          customTimePeriod={customTimePeriod}
          selectedTimePeriodId={selectedTimePeriod?.id}
          onChange={changeSelectedTimePeriod}
        />
        <MousePositionContext.Provider value={mousePositionProps}>
          <ExportablePerformanceGraphWithTimeline
            adjustTimePeriod={adjustTimePeriod}
            customTimePeriod={customTimePeriod}
            getIntervalDates={getIntervalDates}
            graphHeight={280}
            periodQueryParameters={periodQueryParameters}
            resource={details}
            resourceDetailsUpdated={resourceDetailsUpdated}
            selectedTimePeriod={selectedTimePeriod}
          />
        </MousePositionContext.Provider>
      </div>
    </GraphOptionsContext.Provider>
  );
};

const MemoizedGraphTabContent = memoizeComponent<GraphTabContentProps>({
  Component: GraphTabContent,
  memoProps: ['details', 'tabParameters', 'ariaLabel'],
});

const GraphTab = ({ details }: TabProps): JSX.Element => {
  const { tabParameters, setGraphTabParameters } = useResourceContext();

  return (
    <MemoizedGraphTabContent
      details={details}
      setGraphTabParameters={setGraphTabParameters}
      tabParameters={tabParameters}
    />
  );
};

export default GraphTab;
