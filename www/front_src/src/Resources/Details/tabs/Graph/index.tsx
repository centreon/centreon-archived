import { equals, or } from 'ramda';

import { Theme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { TabProps } from '..';
import TimePeriodButtonGroup from '../../../Graph/Performance/TimePeriods';
import ExportablePerformanceGraphWithTimeline from '../../../Graph/Performance/ExportableGraphWithTimeline';
import memoizeComponent from '../../../memoizedComponent';

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

  const type = details?.type as string;
  const equalsService = equals('service');
  const equalsMetaService = equals('metaservice');

  const isService =
    or(equalsService(type), equalsMetaService(type)) ||
    equals(type, 'anomalydetection');

  return (
    <div className={classes.container}>
      {isService ? (
        <>
          <TimePeriodButtonGroup />
          <ExportablePerformanceGraphWithTimeline
            isTabDetails
            graphHeight={280}
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
