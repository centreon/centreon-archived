import * as React from 'react';

import { equals } from 'ramda';

import { Theme, makeStyles } from '@material-ui/core';

import { TabProps } from '..';
import TimePeriodButtonGroup from '../../../Graph/Performance/TimePeriods';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import { ResourceContext, useResourceContext } from '../../../Context';
import memoizeComponent from '../../../memoizedComponent';
import { GraphOptions } from '../../models';
import useGraphOptions, {
  GraphOptionsContext,
} from '../../../Graph/Performance/ExportableGraphWithTimeline/useGraphOptions';

import HostGraph from './HostGraph';

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

  const changeTabGraphOptions = (options: GraphOptions): void => {
    setGraphTabParameters({
      ...tabParameters.graph,
      options,
    });
  };

  const graphOptions = useGraphOptions({
    changeTabGraphOptions,
    options: tabParameters.graph?.options,
  });

  const isService = equals('service', details?.type);

  return (
    <GraphOptionsContext.Provider value={graphOptions}>
      <div className={classes.container}>
        {isService ? (
          <>
            <TimePeriodButtonGroup />
            <ExportablePerformanceGraphWithTimeline
              graphHeight={280}
              resource={details}
            />
          </>
        ) : (
          <HostGraph details={details} />
        )}
      </div>
    </GraphOptionsContext.Provider>
  );
};

const MemoizedGraphTabContent = memoizeComponent<GraphTabContentProps>({
  Component: GraphTabContent,
  memoProps: ['details', 'tabParameters'],
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
