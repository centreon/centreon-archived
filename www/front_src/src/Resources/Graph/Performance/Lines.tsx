import * as React from 'react';

import { Area, Line, YAxis } from 'recharts';
import { pipe, uniq, prop, map, isNil } from 'ramda';

import { fade } from '@material-ui/core';

import { Line as LineModel } from './models';
import formatMetricValue from './formatMetricValue';

import { fontFamily } from '.';

const formatTick = ({ unit, base }) => (value): string => {
  if (isNil(value)) {
    return '';
  }

  return formatMetricValue({ base, unit, value }) as string;
};

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
            tickFormatter={formatTick({ base, unit })}
            yAxisId={unit}
            {...props}
          />
        );
      });
    }

    return [
      <YAxis
        key="single-y-axis"
        tickFormatter={formatTick({ base, unit: '' })}
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
          fill:
            transparency && areaColor && filled
              ? fade(areaColor, transparency * 0.01)
              : 'transparent',
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
