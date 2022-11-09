import { useMemo } from 'react';

import { curveBasis } from '@visx/curve';
import { PatternLines } from '@visx/pattern';
import { Threshold } from '@visx/threshold';
import { difference, equals, isNil, prop } from 'ramda';
import { useAtom } from 'jotai';

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
import { thresholdsAnomalyDetectionDataAtom } from '../../anomalyDetectionAtom';

import { getNewLinesAnomalyDetection } from './helpers';

interface Props {
  graphHeight: number;
  graphWidth: number;
  linesExclusionPeriods: Array<Line>;

  timeSeriesExlusionPeriods: Array<TimeValue>;
}

const AnomalyDetectionExclusionPeriodsThreshold = ({
  data,
  graphHeight,
  graphWidth,
  resource,
  xScale,
  rightScale,
  leftScale,
}: any): JSX.Element | null => {
  const { timeSeries, lines } = data;

  const { newLines } = getNewLinesAnomalyDetection({
    lines,
    resource,
  });

  const [firstUnit, secondUnit, thirdUnit] = getUnits(newLines);

  const stackedLines = getSortedStackedLines(newLines);

  const regularLines = difference(newLines, stackedLines);

  const [{ metric: metricY1, unit: unitY1, invert: invertY1 }] =
    regularLines.filter((item) => equals(item.name, 'Upper Threshold'));

  const [{ metric: metricY0, unit: unitY0, invert: invertY0 }] =
    regularLines.filter((item) => equals(item.name, 'Lower Threshold'));

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
          fillOpacity: 0.8,
        }}
        belowAreaProps={{
          fill: "url('#lines')",
          fillOpacity: 0.8,
        }}
        clipAboveTo={0}
        clipBelowTo={graphHeight}
        curve={curveBasis}
        data={timeSeries}
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
