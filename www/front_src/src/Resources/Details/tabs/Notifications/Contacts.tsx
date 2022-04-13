import * as React from 'react';

import { t } from 'i18next';

import { Box, Divider, IconButton, Paper } from '@mui/material';
import SettingsIcon from '@mui/icons-material/Settings';

import { labelConfiguration } from '../../../translatedLabels';

import { ContactGroup, Contact } from './models';

interface Props {
  contacts: Array<Contact> | Array<ContactGroup>;
  getColumns: (contact) => JSX.Element;
  headers: JSX.Element;
  templateColumns: string;
}

const Contacts = ({
  contacts,
  templateColumns,
  getColumns,
  headers,
}: Props): JSX.Element => {
  const goToUri = (uri: string): void => {
    window.location.href = uri;
  };

  const getConfigurationColumn = ({ configuration_uri }): JSX.Element => {
    return (
      <IconButton
        size="small"
        sx={{ justifySelf: 'center', marginRight: 1, width: 'auto' }}
        title={t(labelConfiguration)}
        onClick={(): void => goToUri(configuration_uri)}
      >
        <SettingsIcon color="primary" fontSize="small" />
      </IconButton>
    );
  };

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
      {contacts.map((contact) => {
        return (
          <>
            {getColumns(contact)}
            {getConfigurationColumn(contact)}
          </>
        );
      })}
    </Box>
  );
};

export default Contacts;
