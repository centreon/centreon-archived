import * as React from 'react';

import { useTheme } from '@material-ui/core';
import IconEvent from '@material-ui/icons/Event';

import Chip from '../../../../Chip';

const EventChip = (): JSX.Element => {
  const theme = useTheme();

  return <Chip icon={<IconEvent />} color={theme.palette.primary.main} />;
};

export default EventChip;
