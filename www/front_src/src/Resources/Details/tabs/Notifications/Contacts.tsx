import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Box, Divider, IconButton, Paper, Typography } from '@mui/material';
import SettingsIcon from '@mui/icons-material/Settings';

import {
  labelAlias,
  labelConfiguration,
  labelEmail,
  labelName,
} from '../../../translatedLabels';

const Contacts = ({ contacts, templateColumns }): JSX.Element => {
  const { t } = useTranslation();

  const goToUri = (uri: string): void => {
    window.location.href = uri;
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
        <Typography sx={{ fontWeight: 'bold', paddingLeft: 1 }}>
          {t(labelName)}
        </Typography>
        <Typography sx={{ fontWeight: 'bold' }}>{t(labelAlias)}</Typography>
        <Typography sx={{ fontWeight: 'bold' }}>{t(labelEmail)}</Typography>
        <span />

        <Divider sx={{ gridColumn: '1 / -1' }} />
      </>
      {contacts.map(({ name, alias, email, configuration_uri }) => {
        return (
          <>
            <Typography sx={{ paddingLeft: 1 }}>{name}</Typography>
            <Typography>{alias}</Typography>
            <Typography>{email}</Typography>
            <IconButton
              size="small"
              sx={{ justifySelf: 'center', marginRight: 1, width: 'auto' }}
              title={t(labelConfiguration)}
              onClick={(): void => goToUri(configuration_uri)}
            >
              <SettingsIcon color="primary" fontSize="small" />
            </IconButton>
          </>
        );
      })}
    </Box>
  );
};

export default Contacts;
