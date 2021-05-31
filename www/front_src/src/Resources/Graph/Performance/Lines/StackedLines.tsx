import * as React from 'react';

import { map, nth, pipe, path, all, not, isNil, prop } from 'ramda';
import { AreaStack, curveBasis } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { Line, TimeValue } from '../models';
import { getTime } from '../timeSeries';

import { getFillColor } from '.';

interface Props {
  lines: Array<Line>;
  timeSeries: Array<TimeValue>;
  xScale: ScaleTime<number, number>;
  yScale: ScaleLinear<number, number>;
}

const StackLines = ({
  timeSeries,
  lines,
  yScale,
  xScale,
}: Props): JSX.Element => (
  <AreaStack
    curve={curveBasis}
    data={timeSeries}
    defined={(d): boolean => {
      return pipe(
        map(prop('metric')) as (lines) => Array<string>,
        all((metric) => pipe(path(['data', metric]), isNil, not)(d)),
      )(lines);
    }}
    keys={map(prop('metric'), lines)}
    x={(d): number => xScale(getTime(d.data)) ?? 0}
    y0={(d): number => yScale(d[0]) ?? 0}
    y1={(d): number => yScale(d[1]) ?? 0}
  >
    {({ stacks, path: linePath }): Array<JSX.Element> => {
      return stacks.map((stack, index) => {
        const { areaColor, transparency, lineColor, highlight } = nth(
          index,
          lines,
        ) as Line;
        return (
          <path
            d={linePath(stack) || ''}
            fill={getFillColor({ areaColor, transparency })}
            key={`stack-${prop('key', stack)}`}
            opacity={highlight === false ? 0.3 : 1}
            stroke={lineColor}
            strokeWidth={highlight ? 2 : 1}
          />
        );
      });
    }}
  </AreaStack>
);

export default StackLines;
