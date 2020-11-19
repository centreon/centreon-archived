import * as React from 'react';

import { prop, difference } from 'ramda';
import {
  AreaClosed,
  LinePath,
  curveBasis,
  scaleLinear,
  AreaStack,
} from '@visx/visx';

import { fade } from '@material-ui/core';
import { isNil } from 'lodash';
import { ScaleLinear } from 'd3-scale';
import { Line, TimeValue } from './models';
import {
  getTime,
  getUnits,
  getSortedStackedLines,
  getSpecificTimeSeries,
} from './timeSeries';

interface Props {
  lines: Array<Line>;
  timeSeries: Array<TimeValue>;
  leftScale: ScaleLinear<number, number>;
  rightScale: ScaleLinear<number, number>;
  xScale;
  graphHeight: number;
}

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
  const stackedTimeSeries = getSpecificTimeSeries({
    lines: stackedLines,
    timeSeries,
  });

  const nonStackedLines = difference(lines, stackedLines);

  return (
    <>
      <AreaStack
        data={stackedTimeSeries}
        keys={stackedLines.map((line) => line.metric)}
        x={(d): number => xScale(getTime(d.data)) ?? 0}
        y0={(d): number => leftScale(d[0]) ?? 0}
        y1={(d): number => leftScale(d[1]) ?? 0}
      >
        {({ stacks, path }): Array<JSX.Element> => {
          return stacks.map((stack, index) => {
            const {
              areaColor,
              transparency,
              lineColor,
              highlight,
            } = stackedLines[index];
            return (
              <path
                key={`stack-${stack.key}`}
                d={path(stack) || ''}
                stroke={lineColor}
                fill={
                  transparency
                    ? fade(areaColor, 1 - transparency * 0.01)
                    : undefined
                }
                strokeWidth={highlight ? 2 : 1}
                opacity={highlight === false ? 0.3 : 1}
              />
            );
          });
        }}
      </AreaStack>
      <>
        {nonStackedLines.map(
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
              curve: curveBasis,
              defined: (value): boolean => !isNil(value[metric]),
            };

            if (filled) {
              return (
                <AreaClosed<TimeValue>
                  yScale={yScale}
                  y0={Math.min(yScale(0), graphHeight)}
                  key={metric}
                  fillRule="nonzero"
                  fill={
                    transparency
                      ? fade(areaColor, 1 - transparency * 0.01)
                      : undefined
                  }
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
