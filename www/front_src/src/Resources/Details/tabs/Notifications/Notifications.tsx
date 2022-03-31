import * as React from 'react';

import { isNil, path } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import makeStyles from '@mui/styles/makeStyles';
import { Grid, ListItemIcon, Paper, Tooltip, Typography } from '@mui/material';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import ListSubheader from '@mui/material/ListSubheader';
import SettingsIcon from '@mui/icons-material/Settings';
import ContactsIcon from '@mui/icons-material/Contacts';
import NotificationIconOff from '@mui/icons-material/NotificationsOff';
import ContactMailIcon from '@mui/icons-material/ContactMail';

import { getData, useRequest } from '@centreon/ui';

import { detailsAtom } from '../../detailsAtoms';
import { labelContacts, labelSettings } from '../../../translatedLabels';

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
    paddingTop: theme.spacing(1.5),
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

  if (!notificationContacts?.is_notification_enabled) {
    return <NotificationIconOff color="primary" />;
  }

  return (
    <div>
      {notificationContacts.is_notification_enabled &&
        notificationContacts.contacts?.map(
          ({ id, name, alias, email, configuration_uri }) => (
            <Paper className={classes.paper}>
              <Typography variant="body2">
                <div key={id}>
                  <li>
                    <ContactsIcon color="primary" />
                  </li>
                  {name} //
                  {alias}
                  <Tooltip title={labelSettings}>
                    <li>
                      <SettingsIcon color="primary" href={configuration_uri} />
                    </li>
                  </Tooltip>
                  <li>
                    <ContactMailIcon color="primary" />
                  </li>
                  {email}
                </div>
              </Typography>
            </Paper>
          ),
        )}
      {notificationContacts.is_notification_enabled &&
        notificationContacts.contactGroup?.map(
          ({ id, name, alias, configuration_uri }) => (
            <Grid>
              <Typography variant="body2">
                <div key={id}>
                  <li>{name}</li>/<li>{alias}</li>
                  <li>{configuration_uri}</li>
                </div>
              </Typography>
            </Grid>
          ),
        )}
    </div>
  );
};

//   return (
//     <div>
//       <List className={classes.list}>
//           <ListSubheader>{`${(labelContacts)}`}</ListSubheader>
//           {notificationContacts?.is_notification_enabled &&
//             notificationContacts.contacts?.map(
//               ({ id, name, alias, email, configuration_uri }) => (
//                 <ul key={id}>
//                 <Typography variant="body2">
//                   <ListItem key={`item-${(labelContacts)}-${[notificationContacts.contacts]}`}>
//                       <ContactsIcon color="primary"/>
//                         <ListItemText
//                           primary={`${name}`}
//                           secondary={alias}
//                         />
//                     <ListItemIcon>
//                       <Tooltip title={(labelSettings)} >
//                       <SettingsIcon
//                         href={configuration_uri}
//                         color={
//                           isNil(configuration_uri) ? 'disabled' : 'primary'
//                         }
//                       />
//                       </Tooltip>
//                     </ListItemIcon>
//                   </ListItem>
//                 </Typography>
//                 </ul>
//               ),
//             )}
//       </List>
//     </div>

//   );
// };

export default Notification;
