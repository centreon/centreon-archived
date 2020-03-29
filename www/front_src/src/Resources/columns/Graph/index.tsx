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
      <div style={{ height: 200, width: 475 }}>
        <Graph endpoint="http://localhost:5000/api/beta/graph" />
      </div>
    </HoverChip>
  );
};

export default GraphColumn;
