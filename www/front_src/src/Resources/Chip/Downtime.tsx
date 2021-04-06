import * as React from 'react';

import { useTheme } from '@material-ui/core';

import Chip from '.';
import IconDowntime from '../icons/Downtime';

const DowntimeChip = (): JSX.Element => {
  const theme = useTheme();

  return (
    <Chip
      color={theme.palette.action.inDowntime}
      icon={<IconDowntime fontSize="small" />}
    />
  );
};

export default DowntimeChip;
