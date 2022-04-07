import * as React from 'react';

import { omit, path } from 'ramda';
import { useAtomValue } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import {
  Paper,
  Stack,
  Typography,
} from '@mui/material';
import SettingsIcon from '@mui/icons-material/Settings';
import NotificationIconOff from '@mui/icons-material/NotificationsOff';
import NotificationIconActive from '@mui/icons-material/NotificationsActive';
import PersonIcon from '@mui/icons-material/Person';
import GroupIcon from '@mui/icons-material/Group';

import { getData, useRequest, IconButton } from '@centreon/ui';

import { detailsAtom } from '../../detailsAtoms';
import {
  labelAlias,
  labelContactGroups,
  labelContacts,
  labelEmail,
  labelName,
  labelNotificationStatus,
} from '../../../translatedLabels';

import {  NotificationContacts } from './models';
import Contacts from './Contacts';


const Notification = (): JSX.Element => {
  const { t } = useTranslation();

  const [notificationContacts, setNotificationContacts] =
    React.useState<NotificationContacts | null>(null);

  const { sendRequest } = useRequest<NotificationContacts>({
    request: getData,
  });
  const details = useAtomValue(detailsAtom);

  const endpoint = path(['links', 'endpoints', 'notification_policy'], details);

  const loadNotificationContacts = (): void => {
    sendRequest({ endpoint }).then((retrievedNotificationContacts) => {
      setNotificationContacts(retrievedNotificationContacts);
    });
  };

  React.useEffect(() => {
    loadNotificationContacts();
  }, []);


  const columns = {
    name: {
      header: (<Typography sx={{ fontWeight: 'bold', paddingLeft: 1 }}>
      {t(labelName)}
    </Typography>),
    cell:  (<Typography sx={{ paddingLeft: 1 }}>{Name}</Typography>)
    },
    alias: {
      header: <Typography sx={{ fontWeight: 'bold' }}>{t(labelAlias)}</Typography>,
      cell: ( <Typography>{Alias}</Typography>
        ),

    }, 
     email: {
       heade: <Typography sx={{ fontWeight: 'bold' }}>{t(labelEmail)}</Typography>,
       cell : (<Typography>{Email}</Typography>
       ),
     },
    configuration_uri: {
      header: (<span/>),
      cell: ({<SettingsIcon />}),
    },
  };

  return (
    <Stack spacing={2}>
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
      <Stack alignItems="center" direction="row" padding={1} spacing={0.5}>
        <PersonIcon color="primary" fontSize="large" />
        <Typography sx={{ fontWeight: 'bold' }}>{t(labelContacts)}</Typography>
      </Stack>
     <Contacts contacts={notificationContacts?.contacts || []} templateColumns={columns}/>
      <Stack alignItems="center" direction="row" padding={1} spacing={0.5}>
        <GroupIcon color="primary" fontSize="large" />
        <Typography sx={{ fontWeight: 'bold' }}>
          {t(labelContactGroups)}
        </Typography>
      </Stack>
      <Contacts contacts={notificationContacts?.contact_groups || []} templateColumns={omit(['email'], {columns})}/>
    </Stack>
  );
};

export default Notification;
