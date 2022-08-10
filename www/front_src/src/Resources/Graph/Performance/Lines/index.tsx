import { Scale } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { difference, equals, isNil, max, min } from 'ramda';

import { alpha } from '@mui/material';

import { ResourceType } from '../../../models';
import { Line, TimeValue } from '../models';
import {
  getInvertedStackedLines,
  getMax,
  getMin,
  getNotInvertedStackedLines,
  getSortedStackedLines,
  getTime,
  getTimeSeriesForLines,
  getUnits,
  getYScale,
} from '../timeSeries';

import RegularAnchorPoint from './AnchorPoint/RegularAnchorPoint';
import RegularLine from './RegularLine';
import StackedLines from './StackedLines';
import TresholdAD from './TresholdAD/TresholdAD';

interface Props {
  displayTimeValues: boolean;
  graphHeight: number;
  leftScale: ScaleLinear<number, number>;
  lines: Array<Line>;
  rightScale: ScaleLinear<number, number>;
  timeSeries: Array<TimeValue>;
  timeTick: Date | null;
  type: string;
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
  type,
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

  const isLegendClicked = lines?.length <= 1;

  const isDisplayedTreshold =
    equals(type, ResourceType.anomalydetection) && !isLegendClicked;

  const propsTresholdAD = {
    getTime,
    getYScale,
    graphHeight,
    leftScale,
    regularLines,
    rightScale,
    secondUnit,
    thirdUnit,
    timeSeries,
    xScale,
  };

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
        {isDisplayedTreshold && <TresholdAD {...propsTresholdAD} />}
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
                  lines={lines}
                  metric={metric}
                  resourceType={type}
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
