import * as React from 'react';

import { labelGraph } from '../../translatedLabels';
import HoverChip from '../HoverChip';
import { ColumnProps } from '..';
import GraphChip from '../../Chip/Graph';
import Graph from '../../Graph';

const GraphColumn = ({ row }: ColumnProps): JSX.Element | null => {
  if (!row.performance_graph_endpoint) {
    return null;
  }

  return (
    <HoverChip Chip={(): JSX.Element => <GraphChip />} label={labelGraph}>
      <Graph endpoint={row.performance_graph_endpoint} />
    </HoverChip>
  );
};

export default GraphColumn;
