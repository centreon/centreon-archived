import { map, pipe, reduce, filter, pathOr, addIndex } from 'ramda';

import { Metric, TimeValue, GraphData, Line } from './models';

interface TimeWithMetrics {
  time: number;
  metrics: Array<Metric>;
}

const toTimeWithMetrics = ({ metrics, times }): Array<TimeWithMetrics> => {
  return map(
    (time) => ({
      time,
      metrics,
    }),
    times,
  );
};

const toTimeValue = (
  { time, metrics }: TimeWithMetrics,
  timeIndex: number,
): TimeValue => {
  const getMetricsForIndex = (): TimeValue => {
    const addMetricForTimeIndex = (acc, { metric, data }): TimeValue => ({
      ...acc,
      [metric]: data[timeIndex],
    });

    return reduce(addMetricForTimeIndex, {}, metrics);
  };

  return { time, ...getMetricsForIndex() };
};

const getTimeSeries = (graphData: GraphData): Array<TimeValue> => {
  const isGreaterThanLowerLimit = (value): boolean =>
    value > pathOr(value - 1, ['global', 'lower-limit'], graphData);

  const rejectLowerThanLimit = ({ time, ...metrics }: TimeValue): TimeValue => {
    return {
      ...filter(isGreaterThanLowerLimit, metrics),
      time,
    };
  };

  const indexedMap = addIndex<TimeWithMetrics, TimeValue>(map);

  return pipe(
    toTimeWithMetrics,
    indexedMap(toTimeValue),
    map(rejectLowerThanLimit),
  )(graphData);
};

const toLine = ({ ds_data, legend, metric, unit }: Metric): Line => ({
  metric,
  name: legend,
  color: ds_data.ds_color_line,
  areaColor: ds_data.ds_color_area,
  transparency: ds_data.ds_transparency,
  lineColor: ds_data.ds_color_line,
  filled: ds_data.ds_filled,
  unit,
  display: true,
  highlight: undefined,
});

const getLineData = (graphData: GraphData): Array<Line> => {
  return map(toLine, graphData.metrics);
};

export default getTimeSeries;
export { getLineData };
