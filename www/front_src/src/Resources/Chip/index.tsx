import * as React from 'react';

import { Box } from '@mui/material';

interface Props {
  color?: string;
  icon: JSX.Element;
}

const Chip = ({ icon, color }: Props): JSX.Element => {
  return (
    <Box
      sx={{
        height: 2.5,
        width: 2.5,
        ...(color && {
          color,
        }),
      }}
    >
      {icon}
    </Box>
  );
};

export default Chip;
