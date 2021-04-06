import * as React from 'react';

import { Typography, Grid, makeStyles, Box } from '@material-ui/core';
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
  labelNo,
} from '../../../../translatedLabels';
import { getFormattedDate, getFormattedTime } from '../../../../dateTime';
import { ResourceDetails } from '../../../models';

type Lines = Array<{ key: string; line: JSX.Element | null }>;

interface DetailCardLines {
  field?: string | number | boolean;
  getLines: () => Lines;
  title: string;
}

const DetailsLine = ({ line }: { line?: string }): JSX.Element => {
  return (
    <Typography component="div">
      <Box fontWeight={500} lineHeight={1}>
        {line}
      </Box>
    </Typography>
  );
};

const useStyles = makeStyles((theme) => ({
  activeIcon: {
    color: theme.palette.success.main,
  },
}));

const ActiveLine = (): JSX.Element => {
  const classes = useStyles();

  return (
    <Grid container alignItems="center" spacing={1}>
      <Grid item>
        <IconCheck className={classes.activeIcon} />
      </Grid>
      <Grid item>
        <DetailsLine key="tries" line={labelActive} />
      </Grid>
    </Grid>
  );
};

const getDetailCardLines = (
  details: ResourceDetails,
): Array<DetailCardLines> => {
  const getDateTimeLines = ({ label, field }): DetailCardLines => ({
    field,
    getLines: (): Lines => [
      {
        key: `${label}_date`,
        line: <DetailsLine line={getFormattedDate(field)} />,
      },
      {
        key: `${label}_time`,
        line: <DetailsLine key="tries" line={getFormattedTime(field)} />,
      },
    ],
    title: label,
  });

  const getCheckLines = ({ label, field }): DetailCardLines => ({
    ...getDateTimeLines({ field, label }),
    getLines: (): Lines => [
      ...getDateTimeLines({ field, label }).getLines(),
      {
        key: `${label}_active`,
        line: details.active_checks ? <ActiveLine /> : null,
      },
    ],
  });

  return [
    {
      field: details.poller_name,
      getLines: (): Lines => [
        {
          key: 'poller',
          line: <DetailsLine line={details.poller_name} />,
        },
      ],
      title: labelPoller,
    },
    {
      field: details.timezone,
      getLines: (): Lines => [
        {
          key: 'timezone',
          line: <DetailsLine line={details.timezone} />,
        },
      ],
      title: labelTimezone,
    },
    {
      field: details.duration,
      getLines: (): Lines => [
        { key: 'duration', line: <DetailsLine line={details.duration} /> },
        {
          key: 'tries',
          line: <DetailsLine key="tries" line={details.tries} />,
        },
      ],
      title: labelCurrentStateDuration,
    },
    getDateTimeLines({
      field: details.last_state_change,
      label: labelLastStateChange,
    }),
    getCheckLines({ field: details.last_check, label: labelLastCheck }),
    getCheckLines({ field: details.next_check, label: labelNextCheck }),
    {
      field: details.execution_time,
      getLines: (): Lines => [
        {
          key: 'check_duration',
          line: <DetailsLine line={`${details.execution_time} s`} />,
        },
      ],
      title: labelCheckDuration,
    },
    {
      field: details.latency,
      getLines: (): Lines => [
        {
          key: 'latency',
          line: <DetailsLine line={`${details.latency} s`} />,
        },
      ],
      title: labelLatency,
    },
    {
      field: details.flapping,
      getLines: (): Lines => [
        {
          key: 'flapping',
          line: <DetailsLine line={details.flapping ? labelYes : labelNo} />,
        },
      ],
      title: labelResourceFlapping,
    },
    {
      field: details.percent_state_change,
      getLines: (): Lines => [
        {
          key: 'percent_state_change',
          line: <DetailsLine line={`${details.percent_state_change}%`} />,
        },
      ],
      title: labelPercentStateChange,
    },
    getDateTimeLines({
      field: details.last_notification,
      label: labelLastNotification,
    }),
    {
      field: details.notification_number,
      getLines: (): Lines => [
        {
          key: 'notification_number',
          line: <DetailsLine line={details.notification_number.toString()} />,
        },
      ],
      title: labelCurrentNotificationNumber,
    },
  ];
};

export default getDetailCardLines;
