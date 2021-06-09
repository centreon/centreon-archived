import * as React from 'react';

import { useTranslation } from 'react-i18next';

import NotificationsOffIcon from '@material-ui/icons/NotificationsOff';
import { Tooltip } from '@material-ui/core';

import { ComponentColumnProps } from '@centreon/ui';

import { labelNotificationDisabled } from '../../translatedLabels';

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

const NotificationColumn = ({
  row,
}: ComponentColumnProps): JSX.Element | null => {
  const { t } = useTranslation();

  if (row.notification_enabled === false) {
    return (
      <IconColumn>
        <NotificationTooltip
          Icon={NotificationsOffIcon}
          title={t(labelNotificationDisabled)}
        />
      </IconColumn>
    );
  }

  return null;
};

export default NotificationColumn;
