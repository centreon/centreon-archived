import { Scale } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';
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
  sortBy,
  add,
  isEmpty,
  any,
  not,
  min,
  max,
} from 'ramda';

import { Metric, TimeValue, GraphData, Line } from '../models';

interface TimeTickWithMetrics {
  metrics: Array<Metric>;
  timeTick: string;
}

const toTimeTickWithMetrics = ({
  metrics,
  times,
}): Array<TimeTickWithMetrics> =>
  map(
    (timeTick) => ({
      metrics,
      timeTick,
    }),
    times,
  );

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
  }: TimeValue): TimeValue => ({
    ...filter(isGreaterThanLowerLimit, metrics),
    timeTick,
  });

  const indexedMap = addIndex<TimeTickWithMetrics, TimeValue>(map);

  return pipe(
    toTimeTickWithMetrics,
    indexedMap(toTimeTickValue),
    map(rejectLowerThanLimit),
  )(graphData);
};

const toLine = ({
  ds_data,
  legend,
  metric,
  unit,
  average_value,
  minimum_value,
  maximum_value,
}: Metric): Line => ({
  areaColor: ds_data.ds_color_area,
  average_value,
  color: ds_data.ds_color_line,
  display: true,
  filled: ds_data.ds_filled,
  highlight: undefined,
  invert: ds_data.ds_invert,
  legend: ds_data.ds_legend,
  lineColor: ds_data.ds_color_line,
  maximum_value,
  metric,
  minimum_value,
  name: legend,
  stackOrder: equals(ds_data.ds_stack, '1')
    ? parseInt(ds_data.ds_order || '0', 10)
    : null,
  transparency: ds_data.ds_transparency,
  unit,
});

const getLineData = (graphData: GraphData): Array<Line> =>
  map(toLine, graphData.metrics);

const getMin = (values: Array<number>): number => Math.min(...values);

const getMax = (values: Array<number>): number => Math.max(...values);

const getTime = (timeValue: TimeValue): number =>
  new Date(timeValue.timeTick).valueOf();

const getMetrics = (timeValue: TimeValue): Array<string> =>
  pipe(keys, reject(equals('timeTick')))(timeValue);

const getValueForMetric =
  (timeValue: TimeValue) =>
  (metric: string): number =>
    prop(metric, timeValue) as number;

const getUnits = (lines: Array<Line>): Array<string> =>
  pipe(map(prop('unit')), uniq)(lines);

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
  const getTimeSeriesValuesForMetric = (metric): Array<number> =>
    map(
      (timeValue) => getValueForMetric(timeValue)(metric),
      timeSeries,
    ) as Array<number>;

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
}: LineForMetricProps): Line | undefined =>
  find(propEq('metric', metric), lines);

interface LinesTimeSeries {
  lines: Array<Line>;
  timeSeries: Array<TimeValue>;
}

const getMetricValuesForLines = ({
  lines,
  timeSeries,
}: LinesTimeSeries): Array<number> =>
  pipe(
    getUnits,
    map((unit) => getMetricValuesForUnit({ lines, timeSeries, unit })),
    flatten,
  )(lines);

const getStackedMetricValues = ({
  lines,
  timeSeries,
}: LinesTimeSeries): Array<number> => {
  const getTimeSeriesValuesForMetric = (metric): Array<number> =>
    map((timeValue) => getValueForMetric(timeValue)(metric), timeSeries);

  const metricsValues = pipe(
    map(prop('metric')) as (metric) => Array<string>,
    map(getTimeSeriesValuesForMetric) as () => Array<Array<number>>,
  )(lines as Array<Line>);

  if (isEmpty(metricsValues) || isNil(metricsValues)) {
    return [];
  }

  return metricsValues[0].map((_, index): number =>
    reduce(
      (acc: number, metricValue: Array<number>) => add(metricValue[index], acc),
      0,
      metricsValues,
    ),
  );
};

const getSortedStackedLines = (lines: Array<Line>): Array<Line> =>
  pipe(
    reject(({ stackOrder }: Line): boolean => isNil(stackOrder)) as (
      lines,
    ) => Array<Line>,
    sortBy(prop('stackOrder')),
  )(lines);

const getInvertedStackedLines = (lines: Array<Line>): Array<Line> =>
  pipe(
    reject(({ invert }: Line): boolean => isNil(invert)) as (
      lines,
    ) => Array<Line>,
    getSortedStackedLines,
  )(lines);

const getNotInvertedStackedLines = (lines: Array<Line>): Array<Line> =>
  pipe(
    filter(({ invert }: Line): boolean => isNil(invert)) as (
      lines,
    ) => Array<Line>,
    getSortedStackedLines,
  )(lines);

interface HasStackedLines {
  lines: Array<Line>;
  unit: string;
}

const hasUnitStackedLines = ({ lines, unit }: HasStackedLines): boolean =>
  pipe(getSortedStackedLines, any(propEq('unit', unit)))(lines);

const getTimeSeriesForLines = ({
  lines,
  timeSeries,
}: LinesTimeSeries): Array<TimeValue> => {
  const metrics = map(prop('metric'), lines);

  return map(
    ({ timeTick, ...metricsValue }): TimeValue => ({
      ...reduce(
        (acc, metric): Omit<TimeValue, 'timePick'> => ({
          ...acc,
          [metric]: metricsValue[metric],
        }),
        {},
        metrics,
      ),
      timeTick,
    }),
    timeSeries,
  );
};

interface GetYScaleProps {
  hasMoreThanTwoUnits: boolean;
  invert: string | null;
  leftScale: ScaleLinear<number, number>;
  rightScale: ScaleLinear<number, number>;
  secondUnit: string;
  unit: string;
}

const getYScale = ({
  hasMoreThanTwoUnits,
  unit,
  secondUnit,
  leftScale,
  rightScale,
  invert,
}: GetYScaleProps): ScaleLinear<number, number> => {
  const isLeftScale = hasMoreThanTwoUnits || unit !== secondUnit;
  const scale = isLeftScale ? leftScale : rightScale;

  return invert
    ? Scale.scaleLinear<number>({
        domain: scale.domain().reverse(),
        nice: true,
        range: scale.range().reverse(),
      })
    : scale;
};

const getScale = ({
  graphValues,
  height,
  stackedValues,
}): ScaleLinear<number, number> => {
  const minValue = min(getMin(graphValues), getMin(stackedValues));
  const maxValue = max(getMax(graphValues), getMax(stackedValues));

  const upperRangeValue = minValue === maxValue && maxValue === 0 ? height : 0;

  return Scale.scaleLinear<number>({
    domain: [minValue, maxValue],
    nice: true,
    range: [height, upperRangeValue],
  });
};

const getLeftScale = ({
  dataLines,
  dataTimeSeries,
  valueGraphHeight,
}: any): any => {
  const [firstUnit, secondUnit, thirdUnit] = getUnits(dataLines);

  const graphValues = isNil(thirdUnit)
    ? getMetricValuesForUnit({
        lines: dataLines,
        timeSeries: dataTimeSeries,
        unit: firstUnit,
      })
    : getMetricValuesForLines({
        lines: dataLines,
        timeSeries: dataTimeSeries,
      });

  const firstUnitHasStackedLines =
    isNil(thirdUnit) && not(isNil(firstUnit))
      ? hasUnitStackedLines({ lines: dataLines, unit: firstUnit })
      : false;

  const stackedValues = firstUnitHasStackedLines
    ? getStackedMetricValues({
        lines: getSortedStackedLines(dataLines),
        timeSeries: dataTimeSeries,
      })
    : [0];

  return getScale({ graphValues, height: valueGraphHeight, stackedValues });
};

const getXScale = ({
  dataTime,
  valueWidth,
}: any): ScaleTime<number, number, never> => {
  return Scale.scaleTime<number>({
    domain: [getMin(dataTime.map(getTime)), getMax(dataTime.map(getTime))],
    range: [0, valueWidth],
  });
};

const getRightScale = ({
  dataLines,
  dataTimeSeries,
  valueGraphHeight,
}: any): any => {
  const [firstUnit, secondUnit, thirdUnit] = getUnits(dataLines);

  const graphValues = getMetricValuesForUnit({
    lines: dataLines,
    timeSeries: dataTimeSeries,
    unit: secondUnit,
  });

  const secondUnitHasStackedLines = isNil(secondUnit)
    ? false
    : hasUnitStackedLines({ lines: dataLines, unit: secondUnit });

  const stackedValues = secondUnitHasStackedLines
    ? getStackedMetricValues({
        lines: getSortedStackedLines(dataLines),
        timeSeries: dataTimeSeries,
      })
    : [0];

  return getScale({ graphValues, height: valueGraphHeight, stackedValues });
};

export {
  getTimeSeries,
  getLineData,
  getMin,
  getMax,
  getTime,
  getMetrics,
  getValueForMetric,
  getMetricValuesForUnit,
  getUnits,
  getDates,
  getLineForMetric,
  getMetricValuesForLines,
  getSortedStackedLines,
  getTimeSeriesForLines,
  getStackedMetricValues,
  getInvertedStackedLines,
  getNotInvertedStackedLines,
  hasUnitStackedLines,
  getYScale,
  getScale,
  getLeftScale,
  getXScale,
  getRightScale,
};
