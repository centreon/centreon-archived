import * as React from 'react';

import { useNavigate } from 'react-router-dom';
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
  const navigate = useNavigate();

  const goToUri = (uri: string): void => {
    navigate(uri);
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
          <React.Fragment key={contact.alias}>
            {getColumns(contact)}
            {getConfigurationColumn(contact)}
          </React.Fragment>
        );
      })}
    </Box>
  );
};

export default Contacts;
