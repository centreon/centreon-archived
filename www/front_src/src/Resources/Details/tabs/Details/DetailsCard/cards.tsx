import { pick, isEmpty, isNil, equals, includes } from 'ramda';

import { SeverityCode } from '@centreon/ui';

import ChecksIcon from '../../../../ChecksIcon';
import {
  labelCurrentStatusDuration,
  labelMonitoringServer,
  labelTimezone,
  labelLastStatusChange,
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
  labelStatusChangePercentage,
  labelStatusInformation,
  labelDowntimeDuration,
  labelAcknowledgement,
  labelPerformanceData,
  labelCommand,
  labelLastCheckWithOkStatus,
  labelCategory,
} from '../../../../translatedLabels';
import { ResourceDetails } from '../../../models';
import ExpandableCard from '../ExpandableCard';
import { ChangeExpandedCardsProps } from '../SortableCards/models';

import DetailsLine from './DetailsLine';
import PercentStateChangeCard from './PercentStateChangeCard';
import Groups from './Groups';
import DowntimesCard from './DowntimesCard';
import AcknowledgementCard from './AcknowledegmentCard';
import CommandLineCard from './CommandLineCard';
import Category from './Category';

export interface DetailCardLine {
  active?: boolean;
  isCustomCard?: boolean;
  line: JSX.Element;
  shouldBeDisplayed: boolean;
  title: string;
  xs?: 6 | 12;
}

interface DetailCardLineProps {
  changeExpandedCards: (props: ChangeExpandedCardsProps) => void;
  details: ResourceDetails;
  expandedCards: Array<string>;
  t: (label: string) => string;
  toDateTime: (date: string | Date) => string;
}

const getDetailCardLines = ({
  details,
  toDateTime,
  t,
  expandedCards,
  changeExpandedCards,
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
          changeExpandedCards={changeExpandedCards}
          content={details.information}
          expandedCard={includes(t(labelStatusInformation), expandedCards)}
          severityCode={details.status.severity_code}
          title={t(labelStatusInformation)}
        />
      ),
      shouldBeDisplayed: !isNil(details.information),
      title: labelStatusInformation,
      xs: 12,
    },
    {
      isCustomCard: true,
      line: <DowntimesCard details={details} />,
      shouldBeDisplayed: !isEmpty(details.downtimes),
      title: labelDowntimeDuration,
      xs: 12,
    },
    {
      isCustomCard: true,
      line: <AcknowledgementCard details={details} />,
      shouldBeDisplayed: !isNil(details.acknowledgement),
      title: labelAcknowledgement,
      xs: 12,
    },
    {
      line: <DetailsLine line={details.fqdn} />,
      shouldBeDisplayed: !isNil(details.fqdn),
      title: labelFqdn,
      xs: 12,
    },
    {
      line: <DetailsLine line={details.alias} />,
      shouldBeDisplayed: !isNil(details.alias),
      title: labelAlias,
    },
    {
      line: <DetailsLine line={details.monitoring_server_name} />,
      shouldBeDisplayed: !isNil(details.monitoring_server_name),
      title: labelMonitoringServer,
    },
    {
      line: <DetailsLine line={details.timezone} />,
      shouldBeDisplayed: !isNil(details.timezone) && !isEmpty(details.timezone),
      title: labelTimezone,
    },
    {
      line: <DetailsLine line={`${details.duration} - ${details.tries}`} />,
      shouldBeDisplayed: !isNil(details.duration),
      title: labelCurrentStatusDuration,
    },
    {
      line: <DetailsLine line={toDateTime(details.last_status_change)} />,
      shouldBeDisplayed: !isNil(details.last_status_change),
      title: labelLastStatusChange,
    },
    {
      line: <DetailsLine line={toDateTime(details.last_check)} />,
      shouldBeDisplayed: !isNil(details.last_check),
      title: labelLastCheck,
    },
    {
      line: <DetailsLine line={toDateTime(details.last_time_with_no_issue)} />,
      shouldBeDisplayed:
        !isNil(details.last_time_with_no_issue) &&
        !equals(details.status.severity_code, SeverityCode.Ok),
      title: labelLastCheckWithOkStatus,
    },
    {
      line: (
        <ChecksIcon {...pick(['active_checks', 'passive_checks'], details)} />
      ),
      shouldBeDisplayed: displayChecksIcon,
      title: labelCheck,
    },
    {
      line: <DetailsLine line={toDateTime(details.next_check)} />,
      shouldBeDisplayed: !isNil(details.next_check),
      title: labelNextCheck,
    },
    {
      line: <DetailsLine line={`${details.execution_time} s`} />,
      shouldBeDisplayed: !isNil(details.execution_time),
      title: labelCheckDuration,
    },
    {
      line: <DetailsLine line={`${details.latency} s`} />,
      shouldBeDisplayed: !isNil(details.latency),
      title: labelLatency,
    },
    {
      line: <PercentStateChangeCard details={details} />,
      shouldBeDisplayed: !isNil(details.percent_state_change),
      title: labelStatusChangePercentage,
    },
    {
      line: <DetailsLine line={toDateTime(details.last_notification)} />,
      shouldBeDisplayed: !isNil(details.last_notification),
      title: labelLastNotification,
    },
    {
      line: <DetailsLine line={details.notification_number.toString()} />,
      shouldBeDisplayed: !isNil(details.notification_number),
      title: labelCurrentNotificationNumber,
    },
    {
      line: <DetailsLine line={details.calculation_type} />,
      shouldBeDisplayed: !isNil(details.calculation_type),
      title: labelCalculationType,
    },
    {
      isCustomCard: true,
      line: <Groups details={details} />,
      shouldBeDisplayed: !isEmpty(details.groups),
      title: labelGroups,
      xs: 12,
    },
    {
      isCustomCard: true,
      line: <Category details={details} />,
      shouldBeDisplayed: !isEmpty(details.categories),
      title: labelCategory,
      xs: 12,
    },
    {
      isCustomCard: true,
      line: (
        <ExpandableCard
          changeExpandedCards={changeExpandedCards}
          content={details.performance_data || ''}
          expandedCard={includes(t(labelPerformanceData), expandedCards)}
          title={t(labelPerformanceData)}
        />
      ),
      shouldBeDisplayed: !isEmpty(details.performance_data),
      title: labelPerformanceData,
      xs: 12,
    },
    {
      isCustomCard: true,
      line: <CommandLineCard details={details} />,
      shouldBeDisplayed: !isNil(details.command_line),
      title: labelCommand,
      xs: 12,
    },
  ];
};

export default getDetailCardLines;
