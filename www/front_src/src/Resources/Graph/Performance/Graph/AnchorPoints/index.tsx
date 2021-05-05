import * as React from 'react';

import { ScaleLinear, ScaleTime } from 'd3-scale';
import { isEmpty, isNil, or, prop } from 'ramda';

import { Line, TimeValue } from '../../models';
import { getUnits, getYScale } from '../../timeSeries';

import AnchorPoint from './Point';

interface Props {
  leftScale: ScaleLinear<number, number>;
  lines: Array<Line>;
  rightScale: ScaleLinear<number, number>;
  timeValue: TimeValue;
  xScale: ScaleTime<number, number>;
}

const AnchorPoints = ({
  rightScale,
  leftScale,
  xScale,
  lines,
  timeValue,
}: Props): JSX.Element => {
  const [, secondUnit, thirdUnit] = getUnits(lines);

  const xAnchorPoint = xScale(new Date(timeValue.timeTick));

  return (
    <>
      {lines.map(
        ({ unit, invert, metric, lineColor, areaColor, transparency }) => {
          const yScale = getYScale({
            hasMoreThanTwoUnits: !isNil(thirdUnit),
            invert,
            leftScale,
            rightScale,
            secondUnit,
            unit,
          });

          const metricValue = prop(metric, timeValue) as
            | number
            | null
            | undefined;

          if (or(isEmpty(metricValue), isNil(metricValue))) {
            return null;
          }

          const yAnchorPoint = yScale(metricValue as number);

          return (
            <AnchorPoint
              areaColor={areaColor}
              key={metric}
              lineColor={lineColor}
              transparency={transparency}
              xAnchorPoint={xAnchorPoint}
              yAnchorPoint={yAnchorPoint}
            />
          );
        },
      )}
    </>
  );
};

export default AnchorPoints;
