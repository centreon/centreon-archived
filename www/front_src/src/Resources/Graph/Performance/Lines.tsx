import * as React from 'react';

import { Area, Line, YAxis } from 'recharts';
import { pipe, uniq, prop, map } from 'ramda';

import { fade } from '@material-ui/core';

import { fontFamily } from '.';
import formatMetricValue from './formatMetricValue';
import { Line as LineModel } from './models';

interface GraphLinesProps {
  base?: number;
  lines: Array<LineModel>;
}

const getGraphLines = ({
  lines,
  base = 1000,
}: GraphLinesProps): Array<JSX.Element> => {
  const getUnits = (): Array<string> => {
    return pipe(map(prop('unit')), uniq)(lines);
  };

  const multipleYAxes = getUnits().length < 3;

  const getYAxes = (): Array<JSX.Element> => {
    const props = { tick: { fontFamily, fontSize: 12 } };

    if (multipleYAxes) {
      return getUnits().map((unit, index) => {
        return (
          <YAxis
            key={unit}
            orientation={index === 0 ? 'left' : 'right'}
            tickFormatter={(tick): string => {
              return formatMetricValue({ base, unit, value: tick });
            }}
            yAxisId={unit}
            {...props}
          />
        );
      });
    }

    return [
      <YAxis
        key="single-y-axis"
        tickFormatter={(tick): string => {
          return formatMetricValue({ base, unit: '', value: tick });
        }}
        {...props}
      />,
    ];
  };

  return [
    ...getYAxes(),
    ...lines.map(
      ({
        metric,
        areaColor,
        transparency,
        lineColor,
        filled,
        unit,
        highlight,
      }) => {
        const props = {
          dataKey: metric,
          dot: false,
          fill: transparency ? fade(areaColor, transparency * 0.01) : undefined,
          isAnimationActive: false,
          opacity: highlight === false ? 0.3 : 1,
          stroke: lineColor,
          strokeWidth: highlight ? 2 : 1,
          unit,
          yAxisId: multipleYAxes ? unit : undefined,
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
