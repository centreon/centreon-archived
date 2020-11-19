import * as React from 'react';

import { map, nth, pipe, path, all, not, isNil, prop } from 'ramda';
import { AreaStack, curveBasis } from '@visx/visx';

import { Line } from './models';
import { getFillColor } from './Lines';
import { getTime } from './timeSeries';

interface StackedLines {
  stackedTimeSeries;
  stackedLines;
  stackedYScale;
  xScale;
}

const StackLines = ({
  stackedTimeSeries,
  stackedLines,
  stackedYScale,
  xScale,
}: StackedLines): JSX.Element => (
  <AreaStack
    data={stackedTimeSeries}
    keys={map(prop('metric'), stackedLines)}
    x={(d): number => xScale(getTime(d.data)) ?? 0}
    y0={(d): number => stackedYScale(d[0]) ?? 0}
    y1={(d): number => stackedYScale(d[1]) ?? 0}
    curve={curveBasis}
    defined={(d): boolean => {
      return pipe(
        map(prop('metric')) as (lines) => Array<string>,
        all((metric) => pipe(path(['data', metric]), isNil, not)(d)),
      )(stackedLines);
    }}
  >
    {({ stacks, path: linePath }): Array<JSX.Element> => {
      return stacks.map((stack, index) => {
        const { areaColor, transparency, lineColor, highlight } = nth(
          index,
          stackedLines,
        ) as Line;
        return (
          <path
            key={`stack-${prop('key', stack)}`}
            d={linePath(stack) || ''}
            stroke={lineColor}
            fill={getFillColor({ transparency, areaColor })}
            strokeWidth={highlight ? 2 : 1}
            opacity={highlight === false ? 0.3 : 1}
          />
        );
      });
    }}
  </AreaStack>
);

export default StackLines;
