import * as React from 'react';

import { useTheme } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';

import Chip from '.';

const AcknowledgeChip = (): JSX.Element => {
  const theme = useTheme();

  return (
    <Chip
      color={theme.palette.action.acknowledged}
      icon={<IconAcknowledge fontSize="small" />}
    />
  );
};

export default AcknowledgeChip;
