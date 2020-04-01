import * as React from 'react';

import { useTheme } from '@material-ui/core';
import IconGraph from '@material-ui/icons/BarChart';

import Chip from '.';

const GraphChip = (): JSX.Element => {
  const theme = useTheme();

  return (
    <Chip
      icon={<IconGraph fontSize="small" />}
      color={theme.palette.common.black}
    />
  );
};

export default GraphChip;
