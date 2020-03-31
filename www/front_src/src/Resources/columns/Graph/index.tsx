import * as React from 'react';

import { labelGraph } from '../../translatedLabels';
import HoverChip from '../HoverChip';
import { ColumnProps } from '..';
import GraphChip from '../../Chip/Graph';
import PerformanceGraph from '../../Graph/Performance';

const GraphColumn = ({ row }: ColumnProps): JSX.Element | null => {
  if (!row.performance_graph_endpoint) {
    return null;
  }

  return (
    <HoverChip Chip={(): JSX.Element => <GraphChip />} label={labelGraph}>
      <div style={{ height: 200, width: 575, display: 'block' }}>
        <PerformanceGraph endpoint="http://localhost:5000/api/beta/graph" />
      </div>
    </HoverChip>
  );
};

export default GraphColumn;
