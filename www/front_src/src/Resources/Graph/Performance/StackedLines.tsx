import * as React from 'react';

import { map, nth, pipe, path, all, not, isNil, prop } from 'ramda';
import { AreaStack, curveBasis } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { Line, TimeValue } from './models';
import { getFillColor } from './Lines';
import { getTime } from './timeSeries';

interface Props {
  timeSeries: Array<TimeValue>;
  lines: Array<Line>;
  yScale: ScaleLinear<number, number>;
  xScale: ScaleTime<number, number>;
}

const StackLines = ({
  timeSeries,
  lines,
  yScale,
  xScale,
}: Props): JSX.Element => (
  <AreaStack
    data={timeSeries}
    keys={map(prop('metric'), lines)}
    x={(d): number => xScale(getTime(d.data)) ?? 0}
    y0={(d): number => yScale(d[0]) ?? 0}
    y1={(d): number => yScale(d[1]) ?? 0}
    curve={curveBasis}
    defined={(d): boolean => {
      return pipe(
        map(prop('metric')) as (lines) => Array<string>,
        all((metric) => pipe(path(['data', metric]), isNil, not)(d)),
      )(lines);
    }}
  >
    {({ stacks, path: linePath }): Array<JSX.Element> => {
      return stacks.map((stack, index) => {
        const { areaColor, transparency, lineColor, highlight } = nth(
          index,
          lines,
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
