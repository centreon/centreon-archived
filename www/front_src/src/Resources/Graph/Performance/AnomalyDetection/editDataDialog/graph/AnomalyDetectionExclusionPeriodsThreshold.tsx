import { useMemo } from 'react';

import { curveBasis } from '@visx/curve';
import { PatternLines } from '@visx/pattern';
import { Threshold } from '@visx/threshold';
import { difference, equals, isNil, prop } from 'ramda';

import { Line, TimeValue } from '../../../models';
import {
  getLeftScale,
  getRightScale,
  getSortedStackedLines,
  getTime,
  getUnits,
  getXScale,
  getYScale,
} from '../../../timeSeries';

interface Props {
  graphHeight: number;
  graphWidth: number;
  linesExclusionPeriods: Array<Line>;

  timeSeriesExlusionPeriods: Array<TimeValue>;
}

const AnomalyDetectionExclusionPeriodsThreshold = ({
  timeSeriesExlusionPeriods,
  graphHeight,
  graphWidth,
  linesExclusionPeriods,
}: Props): JSX.Element | null => {
  const [firstUnit, secondUnit, thirdUnit] = getUnits(linesExclusionPeriods);

  const stackedLines = getSortedStackedLines(linesExclusionPeriods);

  const regularLines = difference(linesExclusionPeriods, stackedLines);

  if (regularLines?.length <= 0) {
    return null;
  }

  const [{ metric: metricY1, unit: unitY1, invert: invertY1 }] =
    regularLines.filter((item) => equals(item.name, 'Upper Threshold'));

  const [{ metric: metricY0, unit: unitY0, invert: invertY0 }] =
    regularLines.filter((item) => equals(item.name, 'Lower Threshold'));

  const leftScale = useMemo(
    () =>
      getLeftScale({
        dataLine: linesExclusionPeriods,
        dataTimeSeries: timeSeriesExlusionPeriods,
        valueGraphHeight: graphHeight,
      }),
    [linesExclusionPeriods, graphHeight, timeSeriesExlusionPeriods],
  );

  const rightScale = useMemo(
    () =>
      getRightScale({
        dataLine: linesExclusionPeriods,
        dataTimeSeries: timeSeriesExlusionPeriods,
        valueGraphHeight: graphHeight,
      }),
    [],
  );

  const xScale = useMemo(
    () =>
      getXScale({
        dataTime: timeSeriesExlusionPeriods,
        valueWidth: graphWidth,
      }),
    [timeSeriesExlusionPeriods, graphWidth],
  );

  const y1Scale = getYScale({
    hasMoreThanTwoUnits: !isNil(thirdUnit),
    invert: invertY1,
    leftScale,
    rightScale,
    secondUnit,
    unit: unitY1,
  });

  const y0Scale = getYScale({
    hasMoreThanTwoUnits: !isNil(thirdUnit),
    invert: invertY0,
    leftScale,
    rightScale,
    secondUnit,
    unit: unitY0,
  });

  const getXPoint = (timeValue): number => {
    return xScale(getTime(timeValue)) as number;
  };
  const getY1Point = (timeValue): number =>
    y1Scale(prop(metricY1, timeValue)) ?? null;
  const getY0Point = (timeValue): number =>
    y0Scale(prop(metricY0, timeValue)) ?? null;

  return (
    <>
      <Threshold
        aboveAreaProps={{
          fill: "url('#lines')",
          fillOpacity: 0.1,
        }}
        belowAreaProps={{
          fill: "url('#lines')",
          fillOpacity: 0.1,
        }}
        clipAboveTo={0}
        clipBelowTo={graphHeight}
        curve={curveBasis}
        data={timeSeriesExlusionPeriods}
        id={`${getY0Point.toString()}${getY1Point.toString()}`}
        x={getXPoint}
        y0={getY0Point}
        y1={getY1Point}
      />
      <PatternLines
        height={5}
        id="lines"
        orientation={['diagonal']}
        stroke="black"
        strokeWidth={1}
        width={5}
      />
    </>
  );
};

export default AnomalyDetectionExclusionPeriodsThreshold;
