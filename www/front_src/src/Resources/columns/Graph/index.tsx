import * as React from 'react';

import { makeStyles } from '@material-ui/core';

import { labelGraph } from '../../translatedLabels';
import HoverChip from '../HoverChip';
import { ColumnProps } from '..';
import GraphChip from '../../Chip/Graph';
import PerformanceGraph from '../../Graph/Performance';

const useStyles = makeStyles((theme) => ({
  graph: {
    display: 'block',
    height: 200,
    width: 575,
    backgroundColor: theme.palette.common.white,
    paddingTop: theme.spacing(1),
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
        Chip={(): JSX.Element => <GraphChip />}
        label={labelGraph}
        onClick={(): void => onClick(row)}
      >
        <div className={classes.graph}>
          <PerformanceGraph endpoint={row.performance_graph_endpoint} />
        </div>
      </HoverChip>
    );
  };

  return GraphHoverChip;
};

export default GraphColumn;
