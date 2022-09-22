import { Shape } from '@visx/visx';
import { NumberValue, ScaleLinear } from 'd3-scale';
import { prop, isNil } from 'ramda';

import { TimeValue } from '../models';

interface AnomalyDetectionShapeCircleProps {
  originMetric: string;
  pointXLower: (item: TimeValue) => number;
  pointXOrigin: (item: TimeValue) => number;
  pointXUpper: (item: TimeValue) => number;
  pointYLower: (item: TimeValue) => number;
  pointYUpper: (item: TimeValue) => number;
  timeSeries: Array<TimeValue>;
  yScale: ScaleLinear<number, number>;
}

const AnomalyDetectionShapeCircle = ({
  timeSeries,
  yScale,
  pointXOrigin,
  pointXLower,
  pointYLower,
  pointYUpper,
  pointXUpper,
  originMetric,
}: AnomalyDetectionShapeCircleProps): JSX.Element => {
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
        const pointX = pointXOrigin(item);
        const pointX1 = pointXLower(item);
        const pointX2 = pointXUpper(item);
        const pointY = yScale(prop(originMetric, item) as NumberValue);
        const pointY1 = pointYLower(item);
        const pointY2 = pointYUpper(item);

        const isPointBetweenPoint1Point2 = isOnLine({
          maxDistance: 0,
          pointX,
          pointX1,
          pointX2,
          pointY,
          pointY1,
          pointY2,
        });

        const isPointsDefined =
          !isNil(pointX) &&
          !isNil(pointY) &&
          !isNil(pointX1) &&
          !isNil(pointY1) &&
          !isNil(pointX2) &&
          !isNil(pointY2);

        return (
          !isPointBetweenPoint1Point2 &&
          isPointsDefined && (
            <Shape.Circle
              cx={pointX}
              cy={pointY}
              fill="red"
              // fillOpacity="50%"
              key={index.toString()}
              r={2}
            />
          )
        );
      })}
    </>
  );
};

export default AnomalyDetectionShapeCircle;
