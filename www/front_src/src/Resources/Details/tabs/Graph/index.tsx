import { equals } from 'ramda';

import { Theme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { TabProps } from '..';
import TimePeriodButtonGroup from '../../../Graph/Performance/TimePeriods';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import memoizeComponent from '../../../memoizedComponent';
import { ResourceType } from '../../../models';

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
  const equalsAD = equals(ResourceType.anomalydetection);

  const isService =
    equalsService(type) || equalsMetaService(type) || equalsAD(type);

  return (
    <div className={classes.container}>
      {isService ? (
        <>
          <TimePeriodButtonGroup />
          <ExportablePerformanceGraphWithTimeline
            graphHeight={280}
            isModalADOpened={false}
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
