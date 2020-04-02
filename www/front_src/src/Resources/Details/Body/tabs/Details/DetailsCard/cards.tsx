import * as React from 'react';

import { Typography, Grid, makeStyles } from '@material-ui/core';
import IconCheck from '@material-ui/icons/Check';

import {
  labelCurrentStateDuration,
  labelPoller,
  labelTimezone,
  labelLastStateChange,
  labelLastCheck,
  labelActive,
  labelNextCheck,
  labelCheckDuration,
  labelLatency,
  labelResourceFlapping,
  labelYes,
  labelPercentStateChange,
  labelLastNotification,
  labelCurrentNotificationNumber,
} from '../../../../../translatedLabels';
import { getFormattedDate, getFormattedTime } from '../../../../../dateTime';
import { ResourceDetails } from '../../../../models';

interface DetailLines {
  title: string;
  field?: string | number | boolean;
  lines: Array<{ key: string; line: JSX.Element | null }>;
}

const DetailsLine = ({ line }: { line?: string }): JSX.Element | null => {
  return line ? <Typography variant="h5">{line}</Typography> : null;
};

const useStyles = makeStyles((theme) => ({
  activeIcon: {
    color: theme.palette.success.main,
  },
}));

const ActiveLine = (): JSX.Element => {
  const classes = useStyles();

  return (
    <Grid container spacing={1} alignItems="center">
      <Grid item>
        <IconCheck className={classes.activeIcon} />
      </Grid>
      <Grid item>
        <DetailsLine key="tries" line={labelActive} />
      </Grid>
    </Grid>
  );
};

const getDetailCardLines = (details: ResourceDetails): Array<DetailLines> => {
  const getDateTimeLines = ({ label, field }): DetailLines => ({
    title: label,
    field,
    lines: [
      {
        key: `${label}_date`,
        line: <DetailsLine line={getFormattedDate(field)} />,
      },
      {
        key: `${label}_time`,
        line: <DetailsLine key="tries" line={getFormattedTime(field)} />,
      },
    ],
  });

  const getCheckLines = ({ label, field }): DetailLines => ({
    ...getDateTimeLines({ label, field }),
    lines: [
      ...getDateTimeLines({ label, field }).lines,
      {
        key: `${label}_active`,
        line: details.active_checks ? <ActiveLine /> : null,
      },
    ],
  });

  return [
    {
      title: labelPoller,
      field: details.poller_name,
      lines: [
        {
          key: 'poller',
          line: <DetailsLine line={details.poller_name} />,
        },
      ],
    },
    {
      title: labelTimezone,
      field: details.timezone,
      lines: [
        {
          key: 'timezone',
          line: <DetailsLine line={details.timezone} />,
        },
      ],
    },
    {
      title: labelCurrentStateDuration,
      field: details.duration,
      lines: [
        { key: 'duration', line: <DetailsLine line={details.duration} /> },
        {
          key: 'tries',
          line: <DetailsLine key="tries" line={details.tries} />,
        },
      ],
    },
    getDateTimeLines({
      label: labelLastStateChange,
      field: details.last_state_change,
    }),
    getCheckLines({ label: labelLastCheck, field: details.last_check }),
    getCheckLines({ label: labelNextCheck, field: details.next_check }),
    {
      title: labelCheckDuration,
      field: details.execution_time,
      lines: [
        {
          key: 'check_duration',
          line: <DetailsLine line={`${details.execution_time} s`} />,
        },
      ],
    },
    {
      title: labelLatency,
      field: details.latency,
      lines: [
        {
          key: 'latency',
          line: <DetailsLine line={`${details.latency} s`} />,
        },
      ],
    },
    {
      title: labelResourceFlapping,
      field: details.flapping,
      lines: [
        {
          key: 'flapping',
          line: <DetailsLine line={details.flapping ? 'N/A' : labelYes} />,
        },
      ],
    },
    {
      title: labelPercentStateChange,
      field: details.percent_state_change,
      lines: [
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
      lines: [
        {
          key: 'notification_number',
          line: <DetailsLine line={details.notification_number.toString()} />,
        },
      ],
    },
  ];
};

export default getDetailCardLines;
