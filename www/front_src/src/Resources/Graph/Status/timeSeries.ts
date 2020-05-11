import { flatten, pipe, map, sortBy } from 'ramda';

import { SeverityCode } from '@centreon/ui';
import { GraphData, Interval } from './models';

interface StatusSeverity {
  status: string;
  severityCode: SeverityCode;
}

const statusWithSeverities: Array<StatusSeverity> = [
  { status: 'critical', severityCode: SeverityCode.High },
  {
    status: 'warning',
    severityCode: SeverityCode.Medium,
  },
  {
    status: 'ok',
    severityCode: SeverityCode.Ok,
  },
  {
    status: 'unknown',
    severityCode: SeverityCode.Low,
  },
];

interface IntervalWithSeverity {
  interval: Interval;
  severityCode: SeverityCode;
}

interface SeverityTimeFraction {
  time: number;
  severityCode: SeverityCode;
  fraction: number;
  offset: number;
  value: 1;
}
const toStartTime = ({ interval }: IntervalWithSeverity): number =>
  interval.start;
const toEndTime = ({ interval }: IntervalWithSeverity): number => interval.end;
const toIntervalWithSeverity = (severityCode: SeverityCode) => (
  interval: Interval,
): IntervalWithSeverity => ({ interval, severityCode });

const getTimeSeries = (graphData: GraphData): Array<SeverityTimeFraction> => {
  const getStatusIntervals = ({
    status,
    severityCode,
  }: StatusSeverity): Array<IntervalWithSeverity> =>
    map(toIntervalWithSeverity(severityCode), graphData[status]);

  const severityIntervals = pipe(
    map(getStatusIntervals),
    flatten,
    sortBy(toStartTime),
  )(statusWithSeverities);

  const minStartTime = Math.min(...map(toStartTime, severityIntervals));
  const maxEndTime = Math.max(...map(toEndTime, severityIntervals));

  const totalElapsedTime = maxEndTime - minStartTime;

  const toSeverityTimeFraction = ({
    interval,
    severityCode,
  }): SeverityTimeFraction => ({
    fraction: (interval.end - interval.start) / totalElapsedTime,
    offset: (interval.start - minStartTime) / totalElapsedTime,
    severityCode,
    time: interval.start,
    value: 1,
  });

  return map(toSeverityTimeFraction, severityIntervals);
};

export default getTimeSeries;
