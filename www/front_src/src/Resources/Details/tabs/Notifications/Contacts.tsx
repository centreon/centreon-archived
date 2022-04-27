import * as React from 'react';

import { t } from 'i18next';
import { isEmpty, isNil } from 'ramda';

import {
  Box,
  Divider,
  IconButton,
  Paper,
  Tooltip,
  Typography,
} from '@mui/material';
import SettingsIcon from '@mui/icons-material/Settings';

import {
  labelConfigure,
  labelNotAuthorizedToAccessConfiguration,
} from '../../../translatedLabels';
import memoizeComponent from '../../../memoizedComponent';

import { ContactGroup, Contact } from './models';

interface Props {
  contacts: Array<Contact> | Array<ContactGroup> | undefined;
  getColumns: (contact) => JSX.Element;
  headers: JSX.Element;
  messageNoContacts: JSX.Element;
  templateColumns: string;
}

const Contacts = ({
  contacts,
  templateColumns,
  getColumns,
  headers,
  messageNoContacts,
}: Props): JSX.Element => {
  const goToUri = (uri): void => {
    window.location.href = uri as string;
  };

  const getConfigurationColumn = React.useCallback(
    ({ configuration_uri }): JSX.Element => {
      const canGoToConfiguration = !isNil(configuration_uri);
      const tooltipTitle = canGoToConfiguration
        ? t(labelConfigure)
        : t(labelNotAuthorizedToAccessConfiguration);
      const iconColor = canGoToConfiguration ? 'primary' : 'default';
      const goToConfiguration = (): void => goToUri(configuration_uri);

      return (
        <Tooltip title={tooltipTitle}>
          <IconButton
            color={iconColor}
            size="small"
            sx={{ justifySelf: 'flex-end', marginRight: 1 }}
            title={t(tooltipTitle)}
            onClick={goToConfiguration}
          >
            <SettingsIcon fontSize="small" />
          </IconButton>
        </Tooltip>
      );
    },
    [],
  );

  if (isEmpty(contacts)) {
    return (
      <Box
        component={Paper}
        display="grid"
        sx={{
          justifyContent: 'center',
          py: 1,
        }}
      >
        <Typography>{messageNoContacts}</Typography>
      </Box>
    );
  }

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
      {contacts?.map((contact) => {
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

const MemoizedConfigurationColumn = memoizeComponent<Props>({
  Component: Contacts,
  memoProps: ['contacts', 'getColumns', 'headers', 'templateColumns'],
});

export default MemoizedConfigurationColumn;
