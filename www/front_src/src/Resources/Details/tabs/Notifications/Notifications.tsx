import { useState, useCallback, useEffect } from 'react';

import { isNil, path } from 'ramda';
import { useAtomValue } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import { Paper, Stack, Typography } from '@mui/material';
import NotificationIconOff from '@mui/icons-material/NotificationsOff';
import NotificationIconActive from '@mui/icons-material/NotificationsActive';
import PersonIcon from '@mui/icons-material/Person';
import GroupIcon from '@mui/icons-material/Group';

import { getData, useRequest } from '@centreon/ui';

import { detailsAtom } from '../../detailsAtoms';
import {
  labelAlias,
  labelContactGroups,
  labelContacts,
  labelEmail,
  labelName,
  labelNoContactGroupsIsConfiguredForThisResource,
  labelNoContactIsConfiguredForThisResource,
  labelNotificationStatus,
} from '../../../translatedLabels';

import { Contact, ContactGroup, NotificationContacts } from './models';
import Contacts from './Contacts';
import ContactsLoadingSkeleton from './ContactsLoadingSkeleton';
import ContactCell from './ContactCell';

const Notifications = (): JSX.Element => {
  const { t } = useTranslation();

  const [notificationContacts, setNotificationContacts] =
    useState<NotificationContacts | null>(null);

  const { sendRequest } = useRequest<NotificationContacts>({
    request: getData,
  });
  const details = useAtomValue(detailsAtom);

  const endpoint = path(['links', 'endpoints', 'notification_policy'], details);

  const loadNotificationContacts = (): void => {
    sendRequest({ endpoint }).then(setNotificationContacts);
  };

  const getContactColumns = useCallback((contact): JSX.Element => {
    return (
      <>
        <ContactCell paddingLeft={1}>{contact.name}</ContactCell>

        <ContactCell>{contact.alias}</ContactCell>
      </>
    );
  }, []);

  const getContactWithEmailColumns = useCallback(
    (contact: Contact): JSX.Element => {
      return (
        <>
          {getContactColumns(contact)}
          <ContactCell>{contact.email}</ContactCell>
        </>
      );
    },
    [],
  );

  useEffect(() => {
    if (isNil(details)) {
      return;
    }

    loadNotificationContacts();
  }, [details]);

  const contactHeaders = (
    <>
      <Typography sx={{ fontWeight: 'bold', paddingLeft: 1 }}>
        {t(labelName)}
      </Typography>
      <Typography sx={{ fontWeight: 'bold' }}>{t(labelAlias)}</Typography>
    </>
  );

  const contactWithEmailHeaders = (
    <>
      {contactHeaders}
      <Typography sx={{ fontWeight: 'bold' }}>{t(labelEmail)}</Typography>
    </>
  );

  if (isNil(notificationContacts)) {
    return <ContactsLoadingSkeleton />;
  }

  return (
    <Stack spacing={1}>
      <Paper>
        <Stack
          alignItems="center"
          direction="row"
          justifyContent="space-between"
          padding={1}
        >
          <Typography sx={{ fontWeight: 'bold' }}>
            {t(labelNotificationStatus)}
          </Typography>
          {notificationContacts?.is_notification_enabled ? (
            <NotificationIconActive color="primary" />
          ) : (
            <NotificationIconOff color="primary" />
          )}
        </Stack>
      </Paper>
      <Stack>
        <Stack alignItems="center" direction="row" padding={1} spacing={0.5}>
          <PersonIcon color="primary" fontSize="large" />
          <Typography sx={{ fontWeight: 'bold' }}>
            {t(labelContacts)}
          </Typography>
        </Stack>
        <Contacts
          contacts={notificationContacts?.contacts as Array<Contact>}
          getColumns={getContactWithEmailColumns}
          headers={contactWithEmailHeaders}
          noContactsMessage={t(labelNoContactIsConfiguredForThisResource)}
          templateColumns="1fr 1fr 1fr 1fr"
        />
      </Stack>
      <Stack>
        <Stack alignItems="center" direction="row" padding={1} spacing={0.5}>
          <GroupIcon color="primary" fontSize="large" />
          <Typography sx={{ fontWeight: 'bold' }}>
            {t(labelContactGroups)}
          </Typography>
        </Stack>
        <Contacts
          contacts={notificationContacts?.contact_groups as Array<ContactGroup>}
          getColumns={getContactColumns}
          headers={contactHeaders}
          noContactsMessage={t(labelNoContactGroupsIsConfiguredForThisResource)}
          templateColumns="1fr 1fr 1fr"
        />
      </Stack>
    </Stack>
  );
};

export default Notifications;
