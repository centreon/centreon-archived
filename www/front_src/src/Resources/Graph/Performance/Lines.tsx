import * as React from 'react';

import { prop } from 'ramda';
import { AreaClosed, LinePath, curveBasis, scaleLinear } from '@visx/visx';

import { fade } from '@material-ui/core';
import { isNil } from 'lodash';
import { ScaleLinear } from 'd3-scale';
import { Line, TimeValue } from './models';
import { getTime, getUnits } from './timeSeries';

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
  );
};

export default Lines;
