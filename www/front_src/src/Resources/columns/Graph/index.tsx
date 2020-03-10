import * as React from 'react';

import { BarChart as IconBarChart } from '@material-ui/icons';

import { labelGraph } from '../../translatedLabels';
import HoverChip from '../HoverChip';

const GraphColumn = ({ Cell, row }): JSX.Element => {
  return (
    <Cell>
      <HoverChip
        ariaLabel={labelGraph}
        Icon={(): JSX.Element => <IconBarChart />}
      >
        Hello
      </HoverChip>
    </Cell>
  );
};

export default GraphColumn;
