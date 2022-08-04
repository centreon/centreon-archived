import { difference, min, max, isNil, prop, equals } from 'ramda';
import { Scale } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { Threshold } from '@visx/threshold';
import { curveBasis } from '@visx/curve';

import { alpha } from '@mui/material';

import { Line, TimeValue } from '../models';
import {
  getUnits,
  getSortedStackedLines,
  getTimeSeriesForLines,
  getMin,
  getMax,
  getNotInvertedStackedLines,
  getInvertedStackedLines,
  getYScale,
  getTime,
} from '../timeSeries';

import RegularLine from './RegularLine';
import RegularAnchorPoint from './AnchorPoint/RegularAnchorPoint';
import StackedLines from './StackedLines';

interface Props {
  displayTimeValues: boolean;
  graphHeight: number;
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
  const [
    {
      metric: metricY1,
      unit: unitY1,
      invert: invertY1,
      lineColor: lineColorY1,
    },
  ] = regularLines.filter((item) =>
    equals(item.metric, 'connection_upper_thresholds'),
  );

  const [
    {
      metric: metricY0,
      unit: unitY0,
      invert: invertY0,
      lineColor: lineColorY0,
    },
  ] = regularLines.filter((item) =>
    equals(item.metric, 'connection_lower_thresholds'),
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

  const X = (timeValue): number => xScale(getTime(timeValue)) as number;
  const Y1 = (timeValue): number => y1Scale(prop(metricY1, timeValue)) ?? null;
  const Y0 = (timeValue): number => y0Scale(prop(metricY0, timeValue)) ?? null;

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
        <Threshold
          aboveAreaProps={{
            fill: lineColorY1,
            fillOpacity: 0.2,
          }}
          belowAreaProps={{
            fill: lineColorY0,
            fillOpacity: 0.2,
          }}
          clipAboveTo={0}
          clipBelowTo={graphHeight}
          curve={curveBasis}
          data={timeSeries}
          id={`${Math.random()}`}
          x={X}
          y0={Y0}
          y1={Y1}
        />
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
                <RegularLine
                  areaColor={areaColor}
                  filled={filled}
                  graphHeight={graphHeight}
                  highlight={highlight}
                  lineColor={lineColor}
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
