import * as React from 'react';

import { Box, Divider, Paper } from '@mui/material';

interface Props {
  contacts: JSX.Element;
  getColumns: () => JSX.Element;
  headers: JSX.Element;
  templateColumns: string;
}

const Contacts = ({
  contacts,
  templateColumns,
  getColumns,
  headers,
}: Props): JSX.Element => {
  return (
    <Box
      component={Paper}
      display="grid"
      sx={{
        alignItems: 'center',
        gap: 1,
        gridTemplateColumns: templateColumns,
        justifyContent: 'center',
        py: 1,
      }}
    >
      <>
        {headers}
        <span />

        <Divider sx={{ gridColumn: '1 / -1' }} />
      </>
      {getColumns(contacts)}
    </Box>
  );
};

export default Contacts;
