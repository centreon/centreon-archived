import * as React from 'react';

import { useTheme } from '@material-ui/core';

import IconDowntime from '../icons/Downtime';

import Chip from '.';

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
