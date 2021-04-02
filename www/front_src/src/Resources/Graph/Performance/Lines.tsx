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
  lines: Array<Line>;
  timeSeries: Array<TimeValue>;
  leftScale: ScaleLinear<number, number>;
  rightScale: ScaleLinear<number, number>;
  xScale: ScaleTime<number, number>;
  graphHeight: number;
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
  transparency: number;
  areaColor: string;
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
        yScale={stackedYScale}
        xScale={xScale}
      />
      <StackedLines
        lines={invertedStackedLines}
        timeSeries={invertedStackedTimeSeries}
        yScale={stackedYScale}
        xScale={xScale}
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
                    range: scale.range().reverse(),
                    nice: true,
                  })
                : scale;
            };

            const yScale = getYScale();

            const props = {
              data: timeSeries,
              unit,
              stroke: lineColor,
              strokeWidth: highlight ? 2 : 1,
              opacity: highlight === false ? 0.3 : 1,
              y: (timeValue): number => yScale(prop(metric, timeValue)) ?? null,
              x: (timeValue): number => xScale(getTime(timeValue)) as number,
              curve: curveLinear,
              defined: (value): boolean => !isNil(value[metric]),
            };

            if (filled) {
              return (
                <AreaClosed<TimeValue>
                  yScale={yScale}
                  y0={Math.min(yScale(0), graphHeight)}
                  key={metric}
                  fillRule="nonzero"
                  fill={getFillColor({ transparency, areaColor })}
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
