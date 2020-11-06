import * as React from 'react';

import { prop } from 'ramda';
import { AreaClosed, LinePath, curveBasis } from '@visx/visx';

import { fade } from '@material-ui/core';
import { Line, TimeValue } from './models';
import { getTime, getUnits } from './timeSeries';

interface Props {
  lines: Array<Line>;
  timeSeries: Array<TimeValue>;
  leftScale;
  rightScale;
  xScale;
}

const Lines = ({
  xScale,
  leftScale,
  rightScale,
  timeSeries,
  lines,
}: Props): JSX.Element => {
  const [leftUnit] = getUnits(lines);

  return (
    <>
      {lines.map(
        ({
          metric,
          areaColor,
          transparency,
          lineColor,
          filled,
          unit,
          highlight,
        }) => {
          const yScale = unit === leftUnit ? leftScale : rightScale;

          const props = {
            data: timeSeries,
            unit,
            stroke: lineColor,
            strokeWidth: highlight ? 2 : 1,
            opacity: highlight === false ? 0.3 : 1,
            y: (timeValue): number => yScale(prop(metric, timeValue)) as number,
            x: (timeValue): number => xScale(getTime(timeValue)) as number,
            curve: curveBasis,
            defined: (value): boolean => value[metric] !== null,
          };

          if (filled) {
            return (
              <AreaClosed<TimeValue>
                yScale={yScale}
                key={metric}
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
  );
};

export default Lines;
