import * as React from 'react';

import { makeStyles, Paper } from '@material-ui/core';
import IconGraph from '@material-ui/icons/BarChart';

import { IconButton, ComponentColumnProps } from '@centreon/ui';

import { labelGraph } from '../../../translatedLabels';
import HoverChip from '../HoverChip';
import PerformanceGraph from '../../../Graph/Performance';

const useStyles = makeStyles((theme) => ({
  graph: {
    display: 'block',
    height: 200,
    overflow: 'auto',
    padding: theme.spacing(2),
    width: 575,
  },
}));

const GraphColumn = ({
  onClick,
}: {
  onClick: (row) => void;
}): ((props: ComponentColumnProps) => JSX.Element | null) => {
  const GraphHoverChip = ({
    row,
  }: ComponentColumnProps): JSX.Element | null => {
    const classes = useStyles();
    if (!row.performance_graph_endpoint) {
      return null;
    }

    return (
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
          <PerformanceGraph
            endpoint={row.performance_graph_endpoint}
            graphHeight={150}
          />
        </Paper>
      </HoverChip>
    );
  };

  return GraphHoverChip;
};

export default GraphColumn;
