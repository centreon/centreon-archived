import * as React from 'react';

import { Grid, Chip } from '@material-ui/core';

import {
  labelCurrentStateDuration,
  labelPoller,
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
import ActiveLine from './ActiveLine';

type Lines = Array<{ key: string; line: JSX.Element | null }>;

interface DetailCardLines {
  title: string;
  field?: string | number | boolean | Array<unknown>;
  xs?: 6 | 12;
  getLines: () => Lines;
}

interface DetailCardLineProps {
  details: ResourceDetails;
  toDate: (date: string | Date) => string;
  toTime: (date: string | Date) => string;
}

const getDetailCardLines = ({
  details,
  toDate,
  toTime,
}: DetailCardLineProps): Array<DetailCardLines> => {
  const getDateTimeLines = ({ label, field }): DetailCardLines => ({
    title: label,
    field,
    getLines: (): Lines => [
      {
        key: `${label}_date`,
        line: <DetailsLine line={toDate(field)} />,
      },
      {
        key: `${label}_time`,
        line: <DetailsLine key="tries" line={toTime(field)} />,
      },
    ],
  });

  const getCheckLines = ({ label, field }): DetailCardLines => ({
    ...getDateTimeLines({ label, field }),
    getLines: (): Lines => [
      ...getDateTimeLines({ label, field }).getLines(),
      {
        key: `${label}_active`,
        line: details.active_checks ? <ActiveLine /> : null,
      },
    ],
  });

  return [
    {
      title: labelFqdn,
      field: details.fqdn,
      xs: 12,
      getLines: (): Lines => [
        {
          key: 'fqdn',
          line: <DetailsLine line={details.fqdn} />,
        },
      ],
    },
    {
      title: labelAlias,
      field: details.alias,
      getLines: (): Lines => [
        {
          key: 'fqdn',
          line: <DetailsLine line={details.alias} />,
        },
      ],
    },
    {
      title: labelPoller,
      field: details.poller_name,
      getLines: (): Lines => [
        {
          key: 'poller',
          line: <DetailsLine line={details.poller_name} />,
        },
      ],
    },
    {
      title: labelTimezone,
      field: details.timezone,
      getLines: (): Lines => [
        {
          key: 'timezone',
          line: <DetailsLine line={details.timezone} />,
        },
      ],
    },
    {
      title: labelCurrentStateDuration,
      field: details.duration,
      getLines: (): Lines => [
        { key: 'duration', line: <DetailsLine line={details.duration} /> },
        {
          key: 'tries',
          line: <DetailsLine key="tries" line={details.tries} />,
        },
      ],
    },
    getDateTimeLines({
      label: labelLastStateChange,
      field: details.last_status_change,
    }),
    getCheckLines({ label: labelLastCheck, field: details.last_check }),
    getCheckLines({ label: labelNextCheck, field: details.next_check }),
    {
      title: labelCheckDuration,
      field: details.execution_time,
      getLines: (): Lines => [
        {
          key: 'check_duration',
          line: <DetailsLine line={`${details.execution_time} s`} />,
        },
      ],
    },
    {
      title: labelLatency,
      field: details.latency,
      getLines: (): Lines => [
        {
          key: 'latency',
          line: <DetailsLine line={`${details.latency} s`} />,
        },
      ],
    },
    {
      title: labelResourceFlapping,
      field: details.flapping,
      getLines: (): Lines => [
        {
          key: 'flapping',
          line: <DetailsLine line={details.flapping ? labelYes : labelNo} />,
        },
      ],
    },
    {
      title: labelPercentStateChange,
      field: details.percent_state_change,
      getLines: (): Lines => [
        {
          key: 'percent_state_change',
          line: <DetailsLine line={`${details.percent_state_change}%`} />,
        },
      ],
    },
    getDateTimeLines({
      label: labelLastNotification,
      field: details.last_notification,
    }),
    {
      title: labelCurrentNotificationNumber,
      field: details.notification_number,
      getLines: (): Lines => [
        {
          key: 'notification_number',
          line: <DetailsLine line={details.notification_number.toString()} />,
        },
      ],
    },
    {
      title: labelGroups,
      field: details.groups,
      xs: 12,
      getLines: (): Lines => [
        {
          key: 'groups',
          line: (
            <Grid container spacing={1}>
              {details.groups?.map((group) => {
                return (
                  <Grid item>
                    <Chip label={group.name} />
                  </Grid>
                );
              })}
            </Grid>
          ),
        },
      ],
    },
  ];
};

export default getDetailCardLines;
