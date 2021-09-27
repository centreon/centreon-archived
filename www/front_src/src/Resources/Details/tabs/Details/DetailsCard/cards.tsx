import * as React from 'react';

import { equals, pick } from 'ramda';

import { SeverityCode } from '@centreon/ui';

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
  labelLastNotification,
  labelCurrentNotificationNumber,
  labelFqdn,
  labelAlias,
  labelGroups,
  labelCalculationType,
  labelCheck,
  labelPercentStateChange,
  labelStatusInformation,
  labelDowntimeDuration,
  labelAcknowledgement,
  labelPerformanceData,
  labelCommand,
  labelLastCheckWithOkStatus,
} from '../../../../translatedLabels';
import { ResourceDetails } from '../../../models';
import ExpandableCard from '../ExpandableCard';

import DetailsLine from './DetailsLine';
import PercentStateChangeCard from './PercentStateChangeCard';
import Groups from './Groups';
import DowntimesCard from './DowntimesCard';
import AcknowledgementCard from './AcknowledegmentCard';
import CommandLineCard from './CommandLineCard';

export interface DetailCardLine {
  active?: boolean;
  isCustomCard?: boolean;
  line: JSX.Element;
  shouldBeDisplayed?: true;
  title: string;
  xs?: 6 | 12;
}

interface DetailCardLineProps {
  details: ResourceDetails;
  t: (label: string) => string;
  toDateTime: (date: string | Date) => string;
}

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
      isCustomCard: true,
      line: (
        <ExpandableCard
          content={details.information}
          severityCode={details.status.severity_code}
          title={t(labelStatusInformation)}
        />
      ),
      shouldBeDisplayed: details.information ? true : undefined,
      title: labelStatusInformation,
      xs: 12,
    },
    {
      isCustomCard: true,
      line: <DowntimesCard details={details} />,
      shouldBeDisplayed: details.downtimes ? true : undefined,
      title: labelDowntimeDuration,
      xs: 12,
    },
    {
      isCustomCard: true,
      line: <AcknowledgementCard details={details} />,
      shouldBeDisplayed: details.acknowledgement ? true : undefined,
      title: labelAcknowledgement,
      xs: 12,
    },
    {
      line: <DetailsLine line={details.fqdn} />,
      shouldBeDisplayed: details.fqdn ? true : undefined,
      title: labelFqdn,
      xs: 12,
    },
    {
      line: <DetailsLine line={details.alias} />,
      shouldBeDisplayed: details.alias ? true : undefined,
      title: labelAlias,
    },
    {
      line: <DetailsLine line={details.monitoring_server_name} />,
      shouldBeDisplayed: details.monitoring_server_name ? true : undefined,
      title: labelMonitoringServer,
    },
    {
      line: <DetailsLine line={details.timezone} />,
      shouldBeDisplayed: details.timezone ? true : undefined,
      title: labelTimezone,
    },
    {
      line: <DetailsLine line={`${details.duration} - ${details.tries}`} />,
      shouldBeDisplayed: details.duration ? true : undefined,
      title: labelCurrentStateDuration,
    },
    {
      line: <DetailsLine line={toDateTime(details.last_status_change)} />,
      shouldBeDisplayed: details.last_status_change ? true : undefined,
      title: labelLastStateChange,
    },
    {
      line: <DetailsLine line={toDateTime(details.last_time_with_no_issue)} />,
      shouldBeDisplayed:
        details.last_time_with_no_issue &&
        !equals(details.status.severity_code, SeverityCode.Ok)
          ? true
          : undefined,
      title: labelLastCheckWithOkStatus,
    },
    {
      line: <DetailsLine line={toDateTime(details.last_check)} />,
      shouldBeDisplayed: details.last_check ? true : undefined,
      title: labelLastCheck,
    },
    {
      line: (
        <ChecksIcon {...pick(['active_checks', 'passive_checks'], details)} />
      ),
      shouldBeDisplayed: displayChecksIcon ? true : undefined,
      title: labelCheck,
    },
    {
      line: <DetailsLine line={toDateTime(details.next_check)} />,
      shouldBeDisplayed: details.next_check ? true : undefined,
      title: labelNextCheck,
    },
    {
      line: <DetailsLine line={`${details.execution_time} s`} />,
      shouldBeDisplayed: details.execution_time ? true : undefined,
      title: labelCheckDuration,
    },
    {
      line: <DetailsLine line={`${details.latency} s`} />,
      shouldBeDisplayed: details.latency ? true : undefined,
      title: labelLatency,
    },
    {
      line: <PercentStateChangeCard details={details} />,
      shouldBeDisplayed: details.percent_state_change ? true : undefined,
      title: labelPercentStateChange,
    },
    {
      line: <DetailsLine line={toDateTime(details.last_notification)} />,
      shouldBeDisplayed: details.last_notification ? true : undefined,
      title: labelLastNotification,
    },
    {
      line: <DetailsLine line={details.notification_number.toString()} />,
      shouldBeDisplayed: details.notification_number ? true : undefined,
      title: labelCurrentNotificationNumber,
    },
    {
      line: <DetailsLine line={details.calculation_type} />,
      shouldBeDisplayed: details.calculation_type ? true : undefined,
      title: labelCalculationType,
    },
    {
      line: <Groups details={details} />,
      shouldBeDisplayed: details.groups ? true : undefined,
      title: labelGroups,
      xs: 12,
    },
    {
      isCustomCard: true,
      line: (
        <ExpandableCard
          content={details.performance_data || ''}
          title={t(labelPerformanceData)}
        />
      ),
      shouldBeDisplayed: details.performance_data ? true : undefined,
      title: labelPerformanceData,
      xs: 12,
    },
    {
      isCustomCard: true,
      line: <CommandLineCard details={details} />,
      shouldBeDisplayed: details.command_line ? true : undefined,
      title: labelCommand,
      xs: 12,
    },
  ];
};

export default getDetailCardLines;
