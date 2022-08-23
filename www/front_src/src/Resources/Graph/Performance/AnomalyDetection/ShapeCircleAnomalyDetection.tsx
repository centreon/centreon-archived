import { Shape } from '@visx/visx';
import { NumberValue, ScaleLinear, ScaleTime } from 'd3-scale';
import { prop } from 'ramda';

import { TimeValue } from '../models';
import { getTime } from '../timeSeries';

interface ShapeCircleAnomalyDetectionProps {
  timeSeries: Array<TimeValue>;
  xScale: ScaleTime<number, number>;
  yScale: ScaleLinear<number, number>;
}

const ShapeCircleAnomalyDetection = ({
  timeSeries,
  xScale,
  yScale,
}: ShapeCircleAnomalyDetectionProps): JSX.Element => {
  const metricPoint = 'connection';
  const metricPoint1 = 'connection_lower_thresholds';
  const metricPoint2 = 'connection_upper_thresholds';

  interface IsOnline {
    maxDistance: number;
    pointX: number;
    pointX1: number;
    pointX2: number;
    pointY: number;
    pointY1: number;
    pointY2: number;
  }

  const isOnLine = ({
    pointX,
    pointY,
    pointX1,
    pointY1,
    pointX2,
    pointY2,
    maxDistance,
  }: IsOnline): boolean => {
    const dxL = pointX2 - pointX1;
    const dyL = pointY2 - pointY1;
    const dxP = pointX - pointX1;
    const dyP = pointY - pointY1;

    const squareLen = dxL * dxL + dyL * dyL;
    const dotProd = dxP * dxL + dyP * dyL;
    const crossProd = dyP * dxL - dxP * dyL;

    const distance = Math.abs(crossProd) / Math.sqrt(squareLen);

    return distance <= maxDistance && dotProd >= 0 && dotProd <= squareLen;
  };

  return (
    <>
      {timeSeries.map((item, index) => {
        const pointX = xScale(getTime(item));
        const pointX1 = xScale(getTime(item));
        const pointX2 = xScale(getTime(item));
        const pointY = yScale(prop(metricPoint, item) as NumberValue);
        const pointY1 = yScale(prop(metricPoint1, item) as NumberValue);
        const pointY2 = yScale(prop(metricPoint2, item) as NumberValue);

        const isPointBetweenPoint1Point2 = isOnLine({
          maxDistance: 0,
          pointX,
          pointX1,
          pointX2,
          pointY,
          pointY1,
          pointY2,
        });

        return (
          !isPointBetweenPoint1Point2 && (
            <Shape.Circle
              cx={pointX}
              cy={pointY}
              fill="red"
              fillOpacity="50%"
              key={index.toString()}
              r={2}
            />
          )
        );
      })}
    </>
  );
};

export default ShapeCircleAnomalyDetection;
