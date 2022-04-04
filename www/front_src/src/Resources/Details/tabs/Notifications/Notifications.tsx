import * as React from 'react';

import { path } from 'ramda';
import { useAtomValue } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import {
  Paper,
  Stack,
  Table,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
} from '@mui/material';
import SettingsIcon from '@mui/icons-material/Settings';
import NotificationIconOff from '@mui/icons-material/NotificationsOff';
import NotificationIconActive from '@mui/icons-material/NotificationsActive';
import PersonIcon from '@mui/icons-material/Person';
import GroupsIcon from '@mui/icons-material/Groups';

import { getData, useRequest } from '@centreon/ui';

import { detailsAtom } from '../../detailsAtoms';
import {
  labelAlias,
  labelConfiguration,
  labelContactGroups,
  labelContacts,
  labelEmail,
  labelName,
  labelNotificationStatus,
} from '../../../translatedLabels';

import { Contact, ContactGroup } from './models';

interface NotificationContacts {
  contact_groups: Array<ContactGroup> | null;
  contacts: Array<Contact> | null;
  is_notification_enabled: boolean;
}

const Notification = (): JSX.Element => {
  const { t } = useTranslation();

  const [notificationContacts, setNotificationContacts] =
    React.useState<NotificationContacts | null>(null);

  const { sendRequest } = useRequest<NotificationContacts>({
    request: getData,
  });
  const details = useAtomValue(detailsAtom);

  const endpoint = path(['links', 'endpoints', 'notification_policy'], details);

  const loadContactNotifs = (): void => {
    sendRequest({ endpoint })
      .then((retrievedNotificationContacts) => {
        setNotificationContacts(retrievedNotificationContacts);
      })
      .catch(() => undefined);
  };

  React.useEffect(() => {
    loadContactNotifs();
  }, []);

  return (
    <Stack spacing={2}>
      <Paper>
        <Stack
          alignItems="center"
          direction="row"
          justifyContent="space-between"
          padding={1}
        >
          <Typography>{t(labelNotificationStatus)}</Typography>
          {notificationContacts?.is_notification_enabled ? (
            <NotificationIconActive color="primary" />
          ) : (
            <NotificationIconOff color="primary" />
          )}
        </Stack>
      </Paper>
      <Stack alignItems="center" direction="row" padding={0.5} spacing={1}>
        <PersonIcon color="primary" />
        <Typography>{t(labelContacts)}</Typography>
      </Stack>
      <Table sx={{ minWidth: 400 }}>
        <TableContainer component={Paper}>
          <TableHead>
            <TableRow>
              <TableCell>{t(labelName)}</TableCell>
              <TableCell>{t(labelAlias)}</TableCell>
              <TableCell>{t(labelEmail)}</TableCell>
              <TableCell align="right" sx={{ minWidth: 180 }}>
                {t(labelConfiguration)}
              </TableCell>
            </TableRow>
          </TableHead>
          {notificationContacts?.contacts?.map(
            ({ id, name, alias, email, configuration_uri }) => {
              return (
                <TableRow key={id}>
                  <TableCell>{name}</TableCell>
                  <TableCell>{alias}</TableCell>
                  <TableCell>{email}</TableCell>
                  <TableCell align="right">
                    <SettingsIcon color="primary" href={configuration_uri} />
                  </TableCell>
                </TableRow>
              );
            },
          )}
        </TableContainer>
      </Table>

      <Stack alignItems="center" direction="row" padding={0.5} spacing={1}>
        <GroupsIcon color="primary" />
        <Typography>{t(labelContactGroups)}</Typography>
      </Stack>

      <Table sx={{ minWidth: 400 }}>
        <TableContainer component={Paper}>
          <TableHead>
            <TableRow>
              <TableCell>{t(labelName)}</TableCell>
              <TableCell>{t(labelAlias)}</TableCell>
              <TableCell align="right" sx={{ minWidth: 250 }}>
                {t(labelConfiguration)}
              </TableCell>
            </TableRow>
          </TableHead>
          {notificationContacts?.contact_groups?.map(
            ({ id, name, alias, configuration_uri }) => {
              return (
                <TableRow
                  key={id}
                  sx={{ '&:last-child td, &:last-child th': { border: 0 } }}
                >
                  <TableCell>{name}</TableCell>
                  <TableCell>{alias}</TableCell>
                  <TableCell align="right">
                    <SettingsIcon color="primary" href={configuration_uri} />
                  </TableCell>
                </TableRow>
              );
            },
          )}
        </TableContainer>
      </Table>
    </Stack>
  );
};

export default Notification;
