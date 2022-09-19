import { useEffect, lazy, Suspense } from 'react';

import { path, isNil, not } from 'ramda';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import { Paper } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import IconGraph from '@mui/icons-material/BarChart';

import {
  IconButton,
  ComponentColumnProps,
  LoadingSkeleton,
} from '@centreon/ui';

import { labelGraph, labelServiceGraphs } from '../../translatedLabels';
import { ResourceDetails } from '../../Details/models';
import { Resource } from '../../models';
import {
  changeMousePositionAndTimeValueDerivedAtom,
  isListingGraphOpenAtom,
} from '../../Graph/Performance/Graph/mouseTimeValueAtoms';
import { graphQueryParametersDerivedAtom } from '../../Graph/Performance/TimePeriods/timePeriodAtoms';
import { lastDayPeriod } from '../../Details/tabs/Graph/models';

import HoverChip from './HoverChip';
import IconColumn from './IconColumn';

const PerformanceGraph = lazy(() => import('../../Graph/Performance'));

const useStyles = makeStyles((theme) => ({
  graph: {
    display: 'block',
    overflow: 'auto',
    padding: theme.spacing(1),
    width: 575,
  },
}));

interface GraphProps {
  displayCompleteGraph: () => void;
  endpoint?: string;
  row: Resource | ResourceDetails;
}

const Graph = ({
  row,
  endpoint,
  displayCompleteGraph,
}: GraphProps): JSX.Element => {
  const getGraphQueryParameters = useAtomValue(graphQueryParametersDerivedAtom);
  const setIsListingGraphOpen = useUpdateAtom(isListingGraphOpenAtom);
  const changeMousePositionAndTimeValue = useUpdateAtom(
    changeMousePositionAndTimeValueDerivedAtom,
  );

  const graphQueryParameters = getGraphQueryParameters({
    timePeriod: lastDayPeriod,
  });

  useEffect(() => {
    setIsListingGraphOpen(true);

    return (): void => {
      setIsListingGraphOpen(false);
      changeMousePositionAndTimeValue({ position: null, timeValue: null });
    };
  }, []);

  return (
    <Suspense fallback={<LoadingSkeleton height="100%" />}>
      <PerformanceGraph
        limitLegendRows
        displayCompleteGraph={displayCompleteGraph}
        displayTitle={false}
        endpoint={`${endpoint}${graphQueryParameters}`}
        graphHeight={150}
        resource={row}
        timeline={[]}
      />
    </Suspense>
  );
};

const renderChip =
  ({ onClick, label }) =>
  (): JSX.Element =>
    (
      <IconButton
        ariaLabel={label}
        size="large"
        title={label}
        onClick={onClick}
      >
        <IconGraph fontSize="small" />
      </IconButton>
    );

const GraphColumn = ({
  onClick,
}: {
  onClick: (row) => void;
}): ((props: ComponentColumnProps) => JSX.Element | null) => {
  const GraphHoverChip = ({
    row,
    isHovered,
  }: ComponentColumnProps): JSX.Element | null => {
    const classes = useStyles();

    const { type } = row;

    const isHost = type === 'host';

    const endpoint = path<string | undefined>(
      ['links', 'endpoints', 'performance_graph'],
      row,
    );

    if (isNil(endpoint) && !isHost) {
      return null;
    }

    const label = isHost ? labelServiceGraphs : labelGraph;

    return (
      <IconColumn>
        <HoverChip
          Chip={renderChip({ label, onClick: () => onClick(row) })}
          isHovered={isHovered}
          label={label}
        >
          {({ close, isChipHovered }): JSX.Element => {
            if (isHost || not(isChipHovered) || not(isHovered)) {
              return <div />;
            }

            return (
              <Paper className={classes.graph}>
                <Graph
                  displayCompleteGraph={(): void => {
                    onClick(row);
                    close();
                  }}
                  endpoint={endpoint}
                  row={row}
                />
              </Paper>
            );
          }}
        </HoverChip>
      </IconColumn>
    );
  };

  return GraphHoverChip;
};

export default GraphColumn;
