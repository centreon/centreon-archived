import { flatten, pipe, prop, map } from 'ramda';

import { SeverityCode } from '@centreon/ui';

interface Interval {
  start: number;
  end: number;
}

interface IntervalWithSeverity {
  interval: Interval;
  severity: SeverityCode;
}

const toIntervalWithSeverity = (severity) => (
  interval,
): IntervalWithSeverity => ({ interval, severity });

const toSeries = (severity) => (intervals): Array<IntervalWithSeverity> =>
  map(toIntervalWithSeverity(severity), intervals);

const getTimeSeries = (graphData): Array<IntervalWithSeverity> => {
  const getStatusSeries = ({ status, severity }): Array<IntervalWithSeverity> =>
    pipe(prop(status), toSeries(severity))(graphData);

  return flatten(
    getStatusSeries({
      status: 'critical',
      severity: SeverityCode.High,
    }),
    getStatusSeries({
      status: 'warning',
      severity: SeverityCode.Medium,
    }),
    getStatusSeries({
      status: 'ok',
      severity: SeverityCode.Ok,
    }),
    getStatusSeries({
      status: 'unknown',
      severity: SeverityCode.Low,
    }),
  );
};

export default getTimeSeries;
