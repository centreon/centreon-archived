import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { pick } from 'ramda';

import { Grid, Chip, Tooltip, makeStyles } from '@material-ui/core';
import FlappingIcon from '@material-ui/icons/SwapCalls';

import ChecksIcon from '../../../../ChecksIcon';
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
  labelLastNotification,
  labelCurrentNotificationNumber,
  labelFqdn,
  labelAlias,
  labelGroups,
  labelCalculationType,
  labelCheck,
  labelFlapping,
} from '../../../../translatedLabels';
import { ResourceDetails } from '../../../models';

import DetailsLine from './DetailsLine';

const useStyles = makeStyles((theme) => ({
  flappingTile: {
    alignItems: 'center',
    columnGap: `${theme.spacing(1)}px`,
    display: 'grid',
    gridTemplateColumns: 'min-content auto',
  },
}));

interface DetailCardLine {
  active?: boolean;
  field?: string | number | boolean | Array<unknown>;
  line: JSX.Element;
  title: string;
  xs?: 6 | 12;
}

interface FlappingtileProps {
  details: ResourceDetails;
}

interface DetailCardLineProps {
  details: ResourceDetails;
  t: (label: string) => string;
  toDateTime: (date: string | Date) => string;
}

const Flappingtile = ({ details }: FlappingtileProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div className={classes.flappingTile}>
      <Tooltip title={t(labelResourceFlapping) as string}>
        <FlappingIcon color="primary" fontSize="small" />
      </Tooltip>
      <DetailsLine line={`${details.percent_state_change}%`} />
    </div>
  );
};

const getDetailCardLines = ({
  details,
  toDateTime,
  t,
}: DetailCardLineProps): Array<DetailCardLine> => {
  const checksDisabled =
    details.active_checks === false && details.passive_checks === false;
  const activeChecksDisabled = details.active_checks === false;

  const displayChecksIcon = checksDisabled || activeChecksDisabled;

  return [
    {
      field: details.fqdn,
      line: <DetailsLine line={details.fqdn} />,
      title: labelFqdn,
      xs: 12,
    },
    {
      field: details.alias,
      line: <DetailsLine line={details.alias} />,
      title: labelAlias,
    },
    {
      field: details.monitoring_server_name,
      line: <DetailsLine line={details.monitoring_server_name} />,
      title: labelMonitoringServer,
    },
    {
      field: details.timezone,
      line: <DetailsLine line={details.timezone} />,
      title: labelTimezone,
    },
    {
      field: details.duration,
      line: <DetailsLine line={`${details.duration} - ${details.tries}`} />,
      title: labelCurrentStateDuration,
    },
    {
      field: details.last_status_change,
      line: <DetailsLine line={toDateTime(details.last_status_change)} />,
      title: labelLastStateChange,
    },
    {
      field: details.last_check,
      line: <DetailsLine line={toDateTime(details.last_check)} />,
      title: labelLastCheck,
    },
    {
      field: displayChecksIcon ? true : undefined,
      line: (
        <ChecksIcon {...pick(['active_checks', 'passive_checks'], details)} />
      ),

      title: labelCheck,
    },
    {
      field: details.next_check,
      line: <DetailsLine line={toDateTime(details.next_check)} />,
      title: labelNextCheck,
    },
    {
      field: details.execution_time,
      line: <DetailsLine line={`${details.execution_time} s`} />,
      title: labelCheckDuration,
    },
    {
      field: details.latency,
      line: <DetailsLine line={`${details.latency} s`} />,
      title: labelLatency,
    },
    {
      field: details.flapping ? true : undefined,
      line: <Flappingtile details={details} />,
      title: labelFlapping,
    },
    {
      field: details.last_notification,
      line: <DetailsLine line={toDateTime(details.last_notification)} />,
      title: labelLastNotification,
    },
    {
      field: details.notification_number,
      line: <DetailsLine line={details.notification_number.toString()} />,
      title: labelCurrentNotificationNumber,
    },
    {
      field: details.calculation_type,
      line: <DetailsLine line={details.calculation_type} />,
      title: labelCalculationType,
    },
    {
      field: details.groups,
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
      title: labelGroups,
      xs: 12,
    },
  ];
};

export default getDetailCardLines;
