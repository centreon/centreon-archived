import { useEffect, useRef } from 'react';

import { Shape } from '@visx/visx';
import { NumberValue, ScaleLinear } from 'd3-scale';
import { isNil, prop } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';

import { TimeValue } from '../models';

import { countedRedCirclesAtom } from './anomalyDetectionAtom';

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
  const setCountedRedCircles = useUpdateAtom(countedRedCirclesAtom);

  const countRedCircles = useRef(0);
  let initCount = 0;

  // setCountedRedCircles(countRedCircles.current);

  console.log({ countRedCircles });

  useEffect(() => {
    console.log('aha', countRedCircles);
    setCountedRedCircles(countRedCircles.current);
  }, [countRedCircles.current]);

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

        if (!isPointBetweenPoint1Point2 && isPointsDefined) {
          initCount += 1;
          countRedCircles.current = initCount;

          return (
            !isPointBetweenPoint1Point2 &&
            isPointsDefined && (
              <Shape.Circle
                cx={pointX}
                cy={pointY}
                fill="red"
                key={index.toString()}
                r={2}
              />
            )
          );
        }
        countRedCircles.current = initCount;

        return null;
      })}
    </>
  );
};

export default AnomalyDetectionShapeCircle;
