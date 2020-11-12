import {
  map,
  pipe,
  reduce,
  filter,
  addIndex,
  isNil,
  path,
  reject,
  equals,
  keys,
  prop,
  flatten,
  propEq,
  uniq,
  find,
} from 'ramda';

import { Metric, TimeValue, GraphData, Line } from '../models';

interface TimeTickWithMetrics {
  timeTick: string;
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
  const getMetricsForIndex = (): Omit<TimeValue, 'timeTick'> => {
    const addMetricForTimeIndex = (acc, { metric, data }): TimeValue => ({
      ...acc,
      [metric]: data[timeIndex],
    });

    return reduce(addMetricForTimeIndex, {} as TimeValue, metrics);
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

  const rejectLowerThanLimit = ({
    timeTick,
    ...metrics
  }: TimeValue): TimeValue => {
    return {
      ...filter(isGreaterThanLowerLimit, metrics),
      timeTick,
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
  invert: ds_data.ds_invert,
  unit,
  display: true,
  highlight: undefined,
});

const getLineData = (graphData: GraphData): Array<Line> => {
  return map(toLine, graphData.metrics);
};

const getMin = (values): number => {
  return Math.min(...values);
};

const getMax = (values): number => {
  return Math.max(...values);
};

const getTime = (timeValue): number => {
  return new Date(timeValue.timeTick).valueOf();
};

const getMetrics = (timeValue: TimeValue): Array<string> => {
  return pipe(keys, reject(equals('timeTick')))(timeValue);
};

const getValueForMetric = (timeValue) => (metric): number =>
  prop(metric, timeValue);

const getMetricValues = (timeValue): Array<number> => {
  return pipe(
    getMetrics,
    map(getValueForMetric(timeValue)),
    reject(isNil),
  )(timeValue);
};

const getUnits = (lines: Array<Line>): Array<string> => {
  return pipe(map(prop('unit')), uniq)(lines);
};

interface ValuesForUnitProps {
  lines: Array<Line>;
  timeSeries: Array<TimeValue>;
  unit: string;
}

const getMetricValuesForUnit = ({
  lines,
  timeSeries,
  unit,
}: ValuesForUnitProps): Array<number> => {
  const getTimeSeriesValuesForMetric = (metric): Array<number> => {
    return map((timeValue) => getValueForMetric(timeValue)(metric), timeSeries);
  };

  return pipe(
    filter(propEq('unit', unit)) as (line) => Array<Line>,
    map(prop('metric')),
    map(getTimeSeriesValuesForMetric),
    flatten,
    reject(isNil),
  )(lines) as Array<number>;
};

const getDates = (timeSeries: Array<TimeValue>): Array<Date> => {
  const toTimeTick = ({ timeTick }: TimeValue): string => timeTick;
  const toDate = (tick: string): Date => new Date(tick);

  return pipe(map(toTimeTick), map(toDate))(timeSeries);
};

interface LineForMetricProps {
  lines: Array<Line>;
  metric: string;
}

const getLineForMetric = ({
  lines,
  metric,
}: LineForMetricProps): Line | undefined => {
  return find(propEq('metric', metric), lines);
};

const getMetricValuesForLines = ({ lines, timeSeries }): Array<number> => {
  return pipe(
    getUnits,
    map((unit) => getMetricValuesForUnit({ unit, lines, timeSeries })),
    flatten,
  )(lines);
};

export {
  getTimeSeries,
  getLineData,
  getMin,
  getMax,
  getTime,
  getMetrics,
  getValueForMetric,
  getMetricValues,
  getMetricValuesForUnit,
  getUnits,
  getDates,
  getLineForMetric,
  getMetricValuesForLines,
};
