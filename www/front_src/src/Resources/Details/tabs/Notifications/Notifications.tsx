import * as React from 'react';

import { isNil, path } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import makeStyles from '@mui/styles/makeStyles';
import { ListItemIcon, Typography } from '@mui/material';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import ListSubheader from '@mui/material/ListSubheader';
import SettingsIcon from '@mui/icons-material/Settings';
import ContactsIcon from '@mui/icons-material/Contacts';

import { getData, useRequest } from '@centreon/ui';

import { detailsAtom } from '../../detailsAtoms';
import { labelContacts } from '../../../translatedLabels';

import { Contact, ContactGroup } from './models';

const useStyles = makeStyles((theme) => ({
  list: {
    bgcolor: 'background.paper',
    maxHeight: theme.spacing(100),
    overflow: 'auto',
    position: 'relative',
    textAlign: 'center',
    width: '100%',
  },
  paper: {
    padding: theme.spacing(2),
    paddingTop: 0,
  },
}));
interface NotificationContacts {
  contactGroup: Array<ContactGroup> | null;
  contacts: Array<Contact> | null;
  is_notification_enabled: boolean;
}

const Notification = (): JSX.Element => {
  const classes = useStyles();

  const [notificationContacts, setNotificationContacts] =
    React.useState<NotificationContacts | null>(null);

  const { sendRequest } = useRequest<NotificationContacts>({
    request: getData,
  });
  const details = useAtomValue(detailsAtom);

  const endpoint = path(['links', 'endpoints', 'notification_policy'], details);

  const loadContactNotifs = (): void => {
    sendRequest({ endpoint: `${endpoint}` })
      .then((retrievedNotificationContacts) => {
        setNotificationContacts(retrievedNotificationContacts);
      })
      .catch(() => undefined);
  };

  React.useEffect(() => {
    loadContactNotifs();
  }, []);

  return (
    <div>
      <List className={classes.list}>
        <Typography variant="body2">
          <ListSubheader>{`${labelContacts}`}</ListSubheader>
          {notificationContacts?.is_notification_enabled &&
            notificationContacts.contacts?.map(
              ({ id, name, alias, email, configuration_uri }) => (
                <Typography variant="body2">
                  <ListItem>
                    <ul>
                      <ContactsIcon color="primary">
                        <ListItemText
                          key={id}
                          primary={`Name:${name} Alias:${alias} Email:${email}`}
                        />
                      </ContactsIcon>
                    </ul>
                    <ListItemIcon>
                      <SettingsIcon
                        href={configuration_uri}
                        color={
                          isNil(configuration_uri) ? 'disabled' : 'primary'
                        }
                      />
                    </ListItemIcon>
                  </ListItem>
                </Typography>
              ),
            )}
        </Typography>
        )
      </List>
    </div>
  );
};

export default Notification;

