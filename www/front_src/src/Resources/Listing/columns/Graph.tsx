import * as React from 'react';

import { path, isNil } from 'ramda';

import { makeStyles, Paper } from '@material-ui/core';
import IconGraph from '@material-ui/icons/esm/BarChart';

import { IconButton, ComponentColumnProps } from '@centreon/ui';

import { labelGraph } from '../../translatedLabels';
import PerformanceGraph from '../../Graph/Performance';
import useMousePosition, {
  MousePositionContext,
} from '../../Graph/Performance/ExportableGraphWithTimeline/useMousePosition';
import { ResourceDetails } from '../../Details/models';
import { Resource } from '../../models';
import useTimePeriod from '../../Graph/Performance/TimePeriods/useTimePeriod';

import HoverChip from './HoverChip';
import IconColumn from './IconColumn';

const useStyles = makeStyles((theme) => ({
  graph: {
    display: 'block',
    overflow: 'auto',
    padding: theme.spacing(1),
    width: 575,
  },
}));

interface GraphProps {
  endpoint?: string;
  row: Resource | ResourceDetails;
}

const Graph = ({ row, endpoint }: GraphProps): JSX.Element => {
  const { periodQueryParameters } = useTimePeriod({});
  const mousePositionProps = useMousePosition();

  return (
    <MousePositionContext.Provider value={mousePositionProps}>
      <PerformanceGraph
        limitLegendRows
        displayTitle={false}
        endpoint={`${endpoint}${periodQueryParameters}`}
        graphHeight={150}
        resource={row}
        timeline={[]}
      />
    </MousePositionContext.Provider>
  );
};

const GraphColumn = ({
  onClick,
}: {
  onClick: (row) => void;
}): ((props: ComponentColumnProps) => JSX.Element | null) => {
  const GraphHoverChip = ({
    row,
  }: ComponentColumnProps): JSX.Element | null => {
    const classes = useStyles();

    const endpoint = path<string | undefined>(
      ['links', 'endpoints', 'performance_graph'],
      row,
    );

    if (isNil(endpoint)) {
      return null;
    }

    return (
      <IconColumn>
        <HoverChip
          Chip={(): JSX.Element => (
            <IconButton
              ariaLabel={labelGraph}
              title={labelGraph}
              onClick={(): void => onClick(row)}
            >
              <IconGraph fontSize="small" />
            </IconButton>
          )}
          label={labelGraph}
        >
          <Paper className={classes.graph}>
            <Graph endpoint={endpoint} row={row} />
          </Paper>
        </HoverChip>
      </IconColumn>
    );
  };

  return GraphHoverChip;
};

export default GraphColumn;
