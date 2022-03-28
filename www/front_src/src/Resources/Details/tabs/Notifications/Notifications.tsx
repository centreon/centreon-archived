import * as React from 'react';

import { path } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import makeStyles from '@mui/styles/makeStyles';
import { Paper, Typography } from '@mui/material';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import ListSubheader from '@mui/material/ListSubheader';

import { getData, useRequest } from '@centreon/ui';

import { detailsAtom } from '../../detailsAtoms';

import { Contact, ContactGroup } from './models';

const useStyles = makeStyles((theme) => ({
  list: {
    bgcolor: 'background.paper',
    maxHeight: theme.spacing(200),
    maxWidth: theme.spacing(200),
    overflow: 'auto',
    position: 'relative',
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
      <List className={classes.list} subheader={<li />}>
        {['CONTACTS', 'CONTACT GROUP'].map((sectionId) => (
          <li key={`section-${sectionId}`}>
            <ul>
              <ListSubheader>{`${sectionId}`}</ListSubheader>
              {['NOM', 'ALIAS', 'EMAIL'].map((item) => (
                <ListItem key={`item-${sectionId}-${item}`}>
                  <ListItemText primary={`Item ${item}`} />
                </ListItem>
              ))}
            </ul>
          </li>
        ))}
      </List>
      {notificationContacts?.is_notification_enabled &&
        notificationContacts.contacts?.map(
          ({ id, name, alias, email, configuration_uri }) => (
            <Paper className={classes.paper}>
              <Typography>
                <div key={id}>
                  <li>{name}</li>/<li>{alias}</li>
                  <li>{email}</li>
                  <li>{configuration_uri}</li>
                </div>
              </Typography>
            </Paper>
          ),
        )}
      {notificationContacts?.is_notification_enabled &&
        notificationContacts.contactGroup?.map(
          ({ id, name, alias, configuration_uri }) => (
            <Paper>
              <Typography>
                <div key={id}>
                  <li>{name}</li>/<li>{alias}</li>
                  <li>{configuration_uri}</li>
                </div>
              </Typography>
            </Paper>
          ),
        )}
    </div>
  );
};

export default Notification;
