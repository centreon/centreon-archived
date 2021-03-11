import * as React from 'react';

import { Grid, Chip } from '@material-ui/core';

import {
  labelCurrentStateDuration,
  labelMonitoringServer,
  labelTimezone,
  labelLastStateChange,
  labelLastCheck,
  labelNextCheck,
  labelCheckDuration,
  labelLatency,
  labelResourceFlapping,
  labelYes,
  labelPercentStateChange,
  labelLastNotification,
  labelCurrentNotificationNumber,
  labelNo,
  labelFqdn,
  labelAlias,
  labelGroups,
} from '../../../../translatedLabels';
import { ResourceDetails } from '../../../models';

import DetailsLine from './DetailsLine';

interface DetailCardLine {
  title: string;
  active?: boolean;
  field?: string | number | boolean | Array<unknown>;
  xs?: 6 | 12;
  line: JSX.Element;
}

interface DetailCardLineProps {
  details: ResourceDetails;
  toDateTime: (date: string | Date) => string;
}

const getDetailCardLines = ({
  details,
  toDateTime,
}: DetailCardLineProps): Array<DetailCardLine> => {
  return [
    {
      title: labelFqdn,
      field: details.fqdn,
      xs: 12,
      line: <DetailsLine line={details.fqdn} />,
    },
    {
      title: labelAlias,
      field: details.alias,
      line: <DetailsLine line={details.alias} />,
    },
    {
      title: labelMonitoringServer,
      field: details.monitoring_server_name,
      line: <DetailsLine line={details.monitoring_server_name} />,
    },
    {
      title: labelTimezone,
      field: details.timezone,
      line: <DetailsLine line={details.timezone} />,
    },
    {
      title: labelCurrentStateDuration,
      field: details.duration,
      line: <DetailsLine line={`${details.duration} - ${details.tries}`} />,
    },
    {
      title: labelLastStateChange,
      field: details.last_status_change,
      line: <DetailsLine line={toDateTime(details.last_status_change)} />,
    },
    {
      title: labelLastCheck,
      field: details.last_check,
      active: details.active_checks,
      line: <DetailsLine line={toDateTime(details.last_check)} />,
    },
    {
      title: labelNextCheck,
      field: details.next_check,
      active: details.active_checks,
      line: <DetailsLine line={toDateTime(details.next_check)} />,
    },
    {
      title: labelCheckDuration,
      field: details.execution_time,
      line: <DetailsLine line={`${details.execution_time} s`} />,
    },
    {
      title: labelLatency,
      field: details.latency,
      line: <DetailsLine line={`${details.latency} s`} />,
    },
    {
      title: labelResourceFlapping,
      field: details.flapping,
      line: <DetailsLine line={details.flapping ? labelYes : labelNo} />,
    },
    {
      title: labelPercentStateChange,
      field: details.percent_state_change,
      line: <DetailsLine line={`${details.percent_state_change}%`} />,
    },
    {
      title: labelLastNotification,
      field: details.last_notification,
      line: <DetailsLine line={toDateTime(details.last_notification)} />,
    },
    {
      title: labelCurrentNotificationNumber,
      field: details.notification_number,
      line: <DetailsLine line={details.notification_number.toString()} />,
    },
    {
      title: labelGroups,
      field: details.groups,
      xs: 12,
      line: (
        <Grid container spacing={1}>
          {details.groups?.map((group) => {
            return (
              <Grid item key={group.name}>
                <Chip label={group.name} />
              </Grid>
            );
          })}
        </Grid>
      ),
    },
  ];
};

export default getDetailCardLines;
