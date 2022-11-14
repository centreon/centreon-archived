import { ReactNode } from 'react';

import { Scale } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { difference, isNil, max, min } from 'ramda';

import { alpha } from '@mui/material';

import { Line, TimeValue } from '../models';
import {
  getInvertedStackedLines,
  getMax,
  getMin,
  getNotInvertedStackedLines,
  getSortedStackedLines,
  getTimeSeriesForLines,
  getUnits,
  getYScale,
} from '../timeSeries';

import RegularAnchorPoint from './AnchorPoint/RegularAnchorPoint';
import RegularLine from './RegularLine';
import StackedLines from './StackedLines';

interface Props {
  anomalyDetectionEnvelope?: ReactNode;
  anomalyDetectionResizeEnvelope: ReactNode;
  displayTimeValues: boolean;
  graphHeight: number;
  isEditAnomalyDetectionDataDialogOpen?: boolean;
  leftScale: ScaleLinear<number, number>;
  lines: Array<Line>;
  rightScale: ScaleLinear<number, number>;
  timeSeries: Array<TimeValue>;
  timeTick: Date | null;
  xScale: ScaleTime<number, number>;
}

interface YScales {
  leftScale: ScaleLinear<number, number>;
  rightScale: ScaleLinear<number, number>;
}

const getStackedYScale = ({
  leftScale,
  rightScale,
}: YScales): ScaleLinear<number, number> => {
  const minDomain = min(
    getMin(leftScale.domain()),
    getMin(rightScale.domain()),
  );
  const maxDomain = max(
    getMax(leftScale.domain()),
    getMax(rightScale.domain()),
  );

  const minRange = min(getMin(leftScale.range()), getMin(rightScale.range()));
  const maxRange = max(getMax(leftScale.range()), getMax(rightScale.range()));

  return Scale.scaleLinear<number>({
    domain: [minDomain, maxDomain],
    nice: true,
    range: [maxRange, minRange],
  });
};

interface FillColor {
  areaColor: string;
  transparency: number;
}

export const getFillColor = ({
  transparency,
  areaColor,
}: FillColor): string | undefined =>
  transparency ? alpha(areaColor, 1 - transparency * 0.01) : undefined;

const Lines = ({
  xScale,
  leftScale,
  rightScale,
  timeSeries,
  lines,
  graphHeight,
  timeTick,
  displayTimeValues,
  isEditAnomalyDetectionDataDialogOpen,
  anomalyDetectionEnvelope,
  anomalyDetectionResizeEnvelope,
}: Props): JSX.Element => {
  const [, secondUnit, thirdUnit] = getUnits(lines);

  const stackedLines = getSortedStackedLines(lines);

  const regularStackedLines = getNotInvertedStackedLines(lines);
  const regularStackedTimeSeries = getTimeSeriesForLines({
    lines: regularStackedLines,
    timeSeries,
  });

  const invertedStackedLines = getInvertedStackedLines(lines);
  const invertedStackedTimeSeries = getTimeSeriesForLines({
    lines: invertedStackedLines,
    timeSeries,
  });

  const stackedYScale = getStackedYScale({ leftScale, rightScale });

  const regularLines = difference(lines, stackedLines);

  return (
    <g>
      <StackedLines
        displayTimeValues={displayTimeValues}
        lines={regularStackedLines}
        timeSeries={regularStackedTimeSeries}
        timeTick={timeTick}
        xScale={xScale}
        yScale={stackedYScale}
      />
      <StackedLines
        displayTimeValues={displayTimeValues}
        lines={invertedStackedLines}
        timeSeries={invertedStackedTimeSeries}
        timeTick={timeTick}
        xScale={xScale}
        yScale={stackedYScale}
      />
      <g>
        {anomalyDetectionEnvelope}
        {anomalyDetectionResizeEnvelope}
        {regularLines.map(
          ({
            metric,
            areaColor,
            transparency,
            lineColor,
            filled,
            unit,
            highlight,
            invert,
          }) => {
            const yScale = getYScale({
              hasMoreThanTwoUnits: !isNil(thirdUnit),
              invert,
              leftScale,
              rightScale,
              secondUnit,
              unit,
            });

            return (
              <g key={metric}>
                {!isEditAnomalyDetectionDataDialogOpen && (
                  <RegularAnchorPoint
                    areaColor={areaColor}
                    displayTimeValues={displayTimeValues}
                    lineColor={lineColor}
                    metric={metric}
                    timeSeries={timeSeries}
                    timeTick={timeTick}
                    transparency={transparency}
                    xScale={xScale}
                    yScale={yScale}
                  />
                )}
                <RegularLine
                  areaColor={areaColor}
                  filled={filled}
                  graphHeight={graphHeight}
                  highlight={highlight}
                  lineColor={lineColor}
                  lines={lines}
                  metric={metric}
                  timeSeries={timeSeries}
                  transparency={transparency}
                  unit={unit}
                  xScale={xScale}
                  yScale={yScale}
                />
              </g>
            );
          },
        )}
      </g>
    </g>
  );
};

export default Lines;
