import { flatten, pipe, prop, map, sortBy, max, min, path } from 'ramda';

import { SeverityCode } from '@centreon/ui';

const statusWithSeverities = [
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

interface Interval {
  start: number;
  end: number;
}

interface IntervalWithSeverity {
  interval: Interval;
  severityCode: SeverityCode;
}

interface SeverityTimeFraction {
  time: number;
  severityCode: SeverityCode;
  fraction: number;
  value: 1;
}

const toEndTime = ({ interval }): number => interval.end;
const toStartTime = ({ interval }): number => interval.start;

const toIntervalWithSeverity = (severityCode) => (
  interval,
): IntervalWithSeverity => ({ interval, severityCode });

const getTimeSeries = (graphData): Array<SeverityTimeFraction> => {
  const getStatusSeries = ({
    status,
    severityCode,
  }): Array<IntervalWithSeverity> =>
    pipe(prop(status), map(toIntervalWithSeverity(severityCode)))(graphData);

  const severityIntervals = pipe(
    map(getStatusSeries),
    flatten,
    sortBy(path(['interval', ['start']])),
  )(statusWithSeverities);

  const minStartTime = min(...map(toStartTime, severityIntervals));
  const maxEndTime = max(...map(toEndTime, severityIntervals));

  const totalElapsedTime = maxEndTime - minStartTime;

  const toSeverityTimeFraction = ({
    interval,
    severityCode,
  }): SeverityTimeFraction => ({
    fraction: (interval.end - interval.start) / totalElapsedTime,
    severityCode,
    time: interval.start,
    value: 1,
  });

  return map(toSeverityTimeFraction, severityIntervals);
};

export default getTimeSeries;
