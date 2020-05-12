import * as React from 'react';

import { Area, Line, YAxis } from 'recharts';
import { pipe, uniq, prop, map } from 'ramda';

import { fade } from '@material-ui/core';

const getGraphLines = ({ lines, formatValue }): Array<JSX.Element> => {
  const getUnits = (): Array<string> => {
    return pipe(map(prop('unit')), uniq)(lines);
  };

  const multipleYAxes = getUnits().length < 3;

  const getYAxes = (): Array<JSX.Element> => {
    const props = { tick: { fontSize: 12 } };

    if (multipleYAxes) {
      return getUnits().map((unit, index) => {
        return (
          <YAxis
            yAxisId={unit}
            key={unit}
            orientation={index === 0 ? 'left' : 'right'}
            tickFormatter={(tick): string => formatValue({ value: tick, unit })}
            {...props}
          />
        );
      });
    }

    return [<YAxis key="single-y-axis" {...props} />];
  };

  return [
    ...getYAxes(),
    ...lines.map(
      ({ metric, areaColor, transparency, lineColor, filled, unit }) => {
        const props = {
          dot: false,
          dataKey: metric,
          unit,
          stroke: lineColor,
          yAxisId: multipleYAxes ? unit : undefined,
          isAnimationActive: false,
          fill: transparency ? fade(areaColor, transparency * 0.01) : undefined,
        };

        if (filled) {
          return <Area key={metric} {...props} />;
        }

        return <Line key={metric} {...props} />;
      },
    ),
  ];
};

export default getGraphLines;
