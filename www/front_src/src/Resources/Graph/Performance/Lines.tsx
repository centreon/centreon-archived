import * as React from 'react';

import { prop, difference, min, max, isNil } from 'ramda';
import { AreaClosed, LinePath, curveLinear, scaleLinear } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { fade } from '@material-ui/core';

import { Line, TimeValue } from './models';
import {
  getTime,
  getUnits,
  getSortedStackedLines,
  getTimeSeriesForLines,
  getMin,
  getMax,
  getNotInvertedStackedLines,
  getInvertedStackedLines,
} from './timeSeries';
import StackedLines from './StackedLines';

interface Props {
  graphHeight: number;
  leftScale: ScaleLinear<number, number>;
  lines: Array<Line>;
  rightScale: ScaleLinear<number, number>;
  timeSeries: Array<TimeValue>;
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

  return scaleLinear<number>({
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
  transparency ? fade(areaColor, 1 - transparency * 0.01) : undefined;

const Lines = ({
  xScale,
  leftScale,
  rightScale,
  timeSeries,
  lines,
  graphHeight,
}: Props): JSX.Element => {
  const [, secondUnit, thirdUnit] = getUnits(lines);

  const hasMoreThanTwoUnits = !isNil(thirdUnit);

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
    <>
      <StackedLines
        lines={regularStackedLines}
        timeSeries={regularStackedTimeSeries}
        xScale={xScale}
        yScale={stackedYScale}
      />
      <StackedLines
        lines={invertedStackedLines}
        timeSeries={invertedStackedTimeSeries}
        xScale={xScale}
        yScale={stackedYScale}
      />
      <>
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
            const getYScale = (): ScaleLinear<number, number> => {
              const isLeftScale = hasMoreThanTwoUnits || unit !== secondUnit;
              const scale = isLeftScale ? leftScale : rightScale;

              return invert
                ? scaleLinear<number>({
                    domain: scale.domain().reverse(),
                    nice: true,
                    range: scale.range().reverse(),
                  })
                : scale;
            };

            const yScale = getYScale();

            const props = {
              curve: curveLinear,
              data: timeSeries,
              defined: (value): boolean => !isNil(value[metric]),
              opacity: highlight === false ? 0.3 : 1,
              stroke: lineColor,
              strokeWidth: highlight ? 2 : 1,
              unit,
              x: (timeValue): number => xScale(getTime(timeValue)) as number,
              y: (timeValue): number => yScale(prop(metric, timeValue)) ?? null,
            };

            if (filled) {
              return (
                <AreaClosed<TimeValue>
                  fill={getFillColor({ areaColor, transparency })}
                  fillRule="nonzero"
                  key={metric}
                  y0={Math.min(yScale(0), graphHeight)}
                  yScale={yScale}
                  {...props}
                />
              );
            }

            return <LinePath<TimeValue> key={metric} {...props} />;
          },
        )}
      </>
    </>
  );
};

export default Lines;
