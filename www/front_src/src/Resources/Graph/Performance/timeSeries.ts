import { map, pipe, reduce, filter, pathOr, addIndex, tap } from 'ramda';

import { Metric, TimeValue, GraphData, Line } from './models';

interface TimeTickWithMetrics {
  timeTick: number;
  metrics: Array<Metric>;
}

const toTimeTickWithMetrics = ({
  metrics,
  times,
}): Array<TimeTickWithMetrics> => {
  return map(
    (timeTick) => ({
      timeTick,
      metrics,
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
  const isGreaterThanLowerLimit = (value): boolean =>
    value >= pathOr(value - 1, ['global', 'lower-limit'], graphData);

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
