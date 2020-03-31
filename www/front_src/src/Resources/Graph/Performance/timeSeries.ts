import { map, prop, pipe, reduce, filter, pathOr, addIndex } from 'ramda';

interface MetricData {
  [metric: string]: string;
}

const toTimeMetrics = ({ metrics, times }) => {
  return map(
    (time) => ({
      time,
      metrics,
    }),
    times,
  );
};

const toSeries = ({ time, metrics }, timeIndex) => {
  const getMetricsForIndex = () => {
    const addMetricForTimeIndex = (acc, { metric, data }) => ({
      ...acc,
      [metric]: data[timeIndex],
    });

    return reduce(addMetricForTimeIndex, {}, metrics);
  };

  return { time, ...getMetricsForIndex() };
};

const getTimeSeries = (graphData) => {
  const isGreaterThanLowerLimit = (value): boolean =>
    value > pathOr(value - 1, ['global', 'lower-limit'], graphData);

  const rejectLowerThanLimit = (timeMetrics) => {
    return {
      ...filter(isGreaterThanLowerLimit, timeMetrics),
      time: timeMetrics.time,
    };
  };

  return pipe(
    toTimeMetrics,
    addIndex(map)(toSeries),
    map(rejectLowerThanLimit),
  )(graphData);
};

export default getTimeSeries;
