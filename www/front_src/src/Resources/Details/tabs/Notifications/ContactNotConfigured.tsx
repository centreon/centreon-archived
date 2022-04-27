import * as React from 'react';

import { Typography, Paper, Stack } from '@mui/material';

interface Props {
  icon: JSX.Element;
  label: string;
  messageLabel: string;
}

const ContactsNotConfigured = ({
  icon,
  label,
  messageLabel,
}: Props): JSX.Element => {
  return (
    <Paper>
      <Stack alignItems="center" direction="row" padding={1} spacing={0.5}>
        {icon}
        <Typography sx={{ fontWeight: 'bold' }}>{label}</Typography>
      </Stack>
      <Stack alignItems="center" padding={1}>
        <Typography>{messageLabel}</Typography>
      </Stack>
    </Paper>
  );
};

export default ContactsNotConfigured;
