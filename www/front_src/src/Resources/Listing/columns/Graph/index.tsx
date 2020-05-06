import * as React from 'react';

import { makeStyles, Paper } from '@material-ui/core';
import IconGraph from '@material-ui/icons/BarChart';

import { labelGraph } from '../../../translatedLabels';
import HoverChip from '../HoverChip';
import { ColumnProps } from '..';
import PerformanceGraph from '../../../Graph/Performance';
import ActionButton from '../../../ActionButton';

const useStyles = makeStyles((theme) => ({
  graph: {
    display: 'block',
    height: 200,
    width: 575,
    padding: theme.spacing(2),
    overflow: 'auto',
  },
}));

const GraphColumn = ({
  onClick,
}: {
  onClick: (row) => void;
}): ((props) => JSX.Element | null) => {
  const GraphHoverChip = ({ row }: ColumnProps): JSX.Element | null => {
    const classes = useStyles();
    if (!row.performance_graph_endpoint) {
      return null;
    }

    return (
      <HoverChip
        Chip={(): JSX.Element => (
          <ActionButton
            title={labelGraph}
            onClick={(): void => onClick(row)}
            ariaLabel={labelGraph}
          >
            <IconGraph fontSize="small" />
          </ActionButton>
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
