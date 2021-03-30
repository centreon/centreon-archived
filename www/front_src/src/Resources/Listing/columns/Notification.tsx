import * as React from 'react';

import NotificationsIcon from '@material-ui/icons/Notifications';
import NotificationsOffIcon from '@material-ui/icons/NotificationsOff';
import { Tooltip } from '@material-ui/core';

import { ComponentColumnProps } from '@centreon/ui';

import {
  labelNotificationDisabled,
  labelNotificationEnabled,
} from '../../translatedLabels';

import IconColumn from './IconColumn';

interface NotificationTooltipProps {
  Icon: (props) => JSX.Element;
  title: string;
}

const NotificationTooltip = ({
  Icon,
  title,
}: NotificationTooltipProps): JSX.Element => {
  const icon = <Icon color="primary" fontSize="small" />;

  return <Tooltip title={title}>{icon}</Tooltip>;
};

const NotificationColumn = ({ row }: ComponentColumnProps): JSX.Element => {
  return (
    <IconColumn>
      {row.notification_enabled ? (
        <NotificationTooltip
          Icon={NotificationsIcon}
          title={labelNotificationEnabled}
        />
      ) : (
        <NotificationTooltip
          Icon={NotificationsOffIcon}
          title={labelNotificationDisabled}
        />
      )}
    </IconColumn>
  );
};

export default NotificationColumn;
