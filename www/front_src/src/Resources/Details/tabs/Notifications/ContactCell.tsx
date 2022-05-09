import { ReactElement } from 'react';

import { Typography, Tooltip } from '@mui/material';

interface Props {
  children: ReactElement | string;
  paddingLeft?: number;
}

const ContactCell = ({ paddingLeft, children }: Props): JSX.Element => {
  return (
    <Tooltip title={children}>
      <Typography
        sx={{
          overflow: 'hidden',
          paddingLeft,
          textOverflow: 'ellipsis',
        }}
      >
        {children}
      </Typography>
    </Tooltip>
  );
};

export default ContactCell;
