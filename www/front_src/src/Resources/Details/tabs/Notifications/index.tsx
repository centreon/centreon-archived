import * as React from 'react';

import { useAtomValue } from 'jotai';


import { detailsAtom } from '../../detailsAtoms';
import Notifications from './Notifications';
import NotificationsOffIcon from '@mui/icons-material/NotificationsOff';


const NotificationTab = (): JSX.Element => {
  const details = useAtomValue(detailsAtom);
 

  return (details?.notification_enabled
      <Notifications /> 
      )
      return <Grid 
      NotificationsOffIcon>
        />
      <ICON/>
      >;
};

export default NotificationTab;
