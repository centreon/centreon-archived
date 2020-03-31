import { map, pipe, reduce, filter, pathOr, addIndex } from 'ramda';

import { Metric, MetricData, TimeWithMetrics } from './models';

interface TimeMetrics {
  time: number;
  metrics: Array<Metric>;
}

const toTimeMetrics = ({ metrics, times }): Array<TimeMetrics> => {
  return map(
    (time) => ({
      time,
      metrics,
    }),
    times,
  );
};

const toTimeWithMetrics = ({ time, metrics }, timeIndex): TimeWithMetrics => {
  const getMetricsForIndex = (): MetricData => {
    const addMetricForTimeIndex = (acc, { metric, data }): MetricData => ({
      ...acc,
      [metric]: data[timeIndex],
    });

    return reduce(addMetricForTimeIndex, {}, metrics);
  };

  return { time, ...getMetricsForIndex() };
};

const getTimeSeries = (graphData): Array<TimeWithMetrics> => {
  const isGreaterThanLowerLimit = (value): boolean =>
    value > pathOr(value - 1, ['global', 'lower-limit'], graphData);

  const rejectLowerThanLimit = (timeMetrics): Array<TimeWithMetrics> => {
    return {
      ...filter(isGreaterThanLowerLimit, timeMetrics),
      time: timeMetrics.time,
    };
  };

  return pipe(
    toTimeMetrics,
    addIndex(map)(toTimeWithMetrics),
    map(rejectLowerThanLimit),
  )(graphData);
};

export default getTimeSeries;
