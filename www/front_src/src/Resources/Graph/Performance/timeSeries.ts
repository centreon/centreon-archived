import { map, pipe, reduce, filter, addIndex, isNil, path } from 'ramda';

import { Metric, TimeValue, GraphData, Line } from './models';

interface TimeTickWithMetrics {
  metrics: Array<Metric>;
  timeTick: number;
}

const toTimeTickWithMetrics = ({
  metrics,
  times,
}): Array<TimeTickWithMetrics> => {
  return map(
    (timeTick) => ({
      metrics,
      timeTick,
    }),
    times,
  );
};

const toTimeTickValue = (
  { timeTick, metrics }: TimeTickWithMetrics,
  timeIndex: number,
): TimeValue => {
  const getMetricsForIndex = (): TimeValue => {
    const addMetricForTimeIndex = (acc, { metric, data }): TimeValue => ({
      ...acc,
      [metric]: data[timeIndex],
    });

    return reduce(addMetricForTimeIndex, {}, metrics);
  };

  return { timeTick, ...getMetricsForIndex() };
};

const getTimeSeries = (graphData: GraphData): Array<TimeValue> => {
  const isGreaterThanLowerLimit = (value): boolean => {
    const lowerLimit = path<number>(['global', 'lower-limit'], graphData);

    if (isNil(lowerLimit)) {
      return true;
    }

    return value >= lowerLimit;
  };

  const rejectLowerThanLimit = ({ time, ...metrics }: TimeValue): TimeValue => {
    return {
      ...filter(isGreaterThanLowerLimit, metrics),
      time,
    };
  };

  const indexedMap = addIndex<TimeTickWithMetrics, TimeValue>(map);

  return pipe(
    toTimeTickWithMetrics,
    indexedMap(toTimeTickValue),
    map(rejectLowerThanLimit),
  )(graphData);
};

const toLine = ({ ds_data, legend, metric, unit }: Metric): Line => ({
  areaColor: ds_data.ds_color_area,
  color: ds_data.ds_color_line,
  display: true,
  filled: ds_data.ds_filled,
  highlight: undefined,
  lineColor: ds_data.ds_color_line,
  metric,
  name: legend,
  transparency: ds_data.ds_transparency,
  unit,
});

const getLineData = (graphData: GraphData): Array<Line> => {
  return map(toLine, graphData.metrics);
};

export default getTimeSeries;
export { getLineData };
