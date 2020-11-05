import * as React from 'react';

import { prop } from 'ramda';
import { AreaClosed, LinePath, curveBasis } from '@visx/visx';

import { fade } from '@material-ui/core';
import { Line, TimeValue } from './models';
import { getTime } from './timeSeries';

interface Props {
  lines: Array<Line>;
  timeSeries: Array<TimeValue>;
  yScale;
  xScale;
}

const Lines = ({ xScale, yScale, timeSeries, lines }: Props): JSX.Element => {
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
          const props = {
            data: timeSeries,
            unit,
            stroke: lineColor,
            strokeWidth: highlight ? 2 : 1,
            opacity: highlight === false ? 0.3 : 1,
            y: (timeValue): number => yScale(prop(metric, timeValue)) as number,
            x: (timeValue): number => xScale(getTime(timeValue)) as number,
            curve: curveBasis,
            yScale,
          };

          if (filled) {
            return (
              <AreaClosed<TimeValue>
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
