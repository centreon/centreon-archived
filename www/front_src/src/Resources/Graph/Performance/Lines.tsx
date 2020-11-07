import * as React from 'react';

import { prop } from 'ramda';
import { AreaClosed, LinePath, curveBasis } from '@visx/visx';

import { fade } from '@material-ui/core';
import { isNil } from 'lodash';
import { ScaleLinear } from 'd3-scale';
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
  const [, secondUnit, thirdUnit] = getUnits(lines);

  const hasMoreThanTwoUnits = !isNil(thirdUnit);

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
          const getYScale = (): ScaleLinear<number, number> => {
            if (hasMoreThanTwoUnits || unit !== secondUnit) {
              return leftScale;
            }

            return rightScale;
          };

          const yScale = getYScale();

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
