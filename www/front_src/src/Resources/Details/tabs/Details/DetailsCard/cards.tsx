import * as React from 'react';

import { isNil, join, path, pick } from 'ramda';
import { ResourceAdditionals } from 'www/front_src/src/Resources/models';

import { Grid, Chip, Tooltip } from '@material-ui/core';
import FlappingIcon from '@material-ui/icons/SwapCalls';

import { getFormattedCalculationMethod } from '../../../../Listing/columns';
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
  labelPercentStateChange,
  labelLastNotification,
  labelCurrentNotificationNumber,
  labelFqdn,
  labelAlias,
  labelGroups,
  labelCalculationType,
  labelCheck,
  labelFlapping,
  labelCalculationMethod,
  labelHealth,
} from '../../../../translatedLabels';
import { ResourceDetails } from '../../../models';

import DetailsLine from './DetailsLine';

interface DetailCardLine {
  active?: boolean;
  field?: string | number | boolean | Array<unknown>;
  line: JSX.Element;
  title: string;
  xs?: 6 | 12;
}

interface DetailCardLineProps {
  details: ResourceDetails;
  t: (label: string) => string;
  toDateTime: (date: string | Date) => string;
}

const getCalculationMethodAndHealthInformations = (
  additionals: ResourceAdditionals | undefined,
): string | undefined => {
  if (isNil(additionals)) {
    return undefined;
  }
  const formattedCalculationMethod = getFormattedCalculationMethod(additionals);

  const formattedHealth = !isNil(additionals.health)
    ? `(${labelHealth}: ${additionals.health})`
    : '';

  return join(' ', [formattedCalculationMethod, formattedHealth]);
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
      line: (
        <Tooltip title={t(labelResourceFlapping)}>
          <FlappingIcon color="primary" />
        </Tooltip>
      ),
      title: labelFlapping,
    },
    {
      field: details.percent_state_change,
      line: <DetailsLine line={`${details.percent_state_change}%`} />,
      title: labelPercentStateChange,
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
      field: path(['additionals', 'calculation_method'], details),
      line: (
        <DetailsLine
          line={
            details
              ? getCalculationMethodAndHealthInformations(details.additionals)
              : ''
          }
        />
      ),
      title: labelCalculationMethod,
    },
    {
      field: path(['additionals', 'health'], details),
      line: <DetailsLine line={`${details.additionals?.health || ''}`} />,
      title: labelHealth,
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
