import { equals } from 'ramda';

import { Theme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { ResourceType } from '../../../models';
import { TabProps } from '..';
import TimePeriodButtonGroup from '../../../Graph/Performance/TimePeriods';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import memoizeComponent from '../../../memoizedComponent';
import useLoadDetails from '../../../Listing/useLoadResources/useLoadDetails';
import AnomalyDetectionGraphActions from '../../../Graph/Performance/AnomalyDetection/editDataDialog/graph/AnomalyDetectionGraphActions';
import { getDisplayAdditionalLinesCondition } from '../../../Graph/Performance/AnomalyDetection/editDataDialog/graph/AnomalyDetectionAdditionalLines';

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

const GraphTabContent = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();

  const type = details?.type as ResourceType;
  const equalsService = equals(ResourceType.service);
  const equalsMetaService = equals(ResourceType.metaservice);
  const equalsAnomalyDetection = equals(ResourceType.anomalyDetection);

  const { loadDetails } = useLoadDetails();

  const isService =
    equalsService(type) ||
    equalsMetaService(type) ||
    equalsAnomalyDetection(type);

  const reload = (value: boolean): void => {
    if (!value) {
      return;
    }
    loadDetails();
  };

  return (
    <div className={classes.container}>
      {isService ? (
        <>
          <TimePeriodButtonGroup />
          <ExportablePerformanceGraphWithTimeline
            interactWithGraph
            getDisplayAdditionalLinesCondition={
              getDisplayAdditionalLinesCondition
            }
            graphHeight={280}
            isRenderAdditionalGraphActions={equalsAnomalyDetection(type)}
            renderAdditionalGraphAction={
              <AnomalyDetectionGraphActions
                details={details}
                sendReloadGraphPerformance={reload}
              />
            }
            resource={details}
          />
        </>
      ) : (
        <HostGraph details={details} />
      )}
    </div>
  );
};

const MemoizedGraphTabContent = memoizeComponent<TabProps>({
  Component: GraphTabContent,
  memoProps: ['details', 'ariaLabel'],
});

const GraphTab = ({ details }: TabProps): JSX.Element => {
  return <MemoizedGraphTabContent details={details} />;
};

export default GraphTab;
