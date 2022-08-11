import { memo } from 'react';

import { Shape, Curve } from '@visx/visx';
import { equals, isNil, prop } from 'ramda';
import { NumberValue, ScaleLinear, ScaleTime } from 'd3-scale';
import { useAtomValue } from 'jotai/utils';

import { getTime } from '../timeSeries';
import { Line, TimeValue } from '../models';
import { ResourceType } from '../../../models';
import { openModalADAtom } from '../AnomalyDetection/anomalyDetectionAtom';

import { getFillColor } from '.';

interface Props {
  areaColor: string;
  filled: boolean;
  graphHeight: number;
  highlight?: boolean;
  lineColor: string;
  lines: Array<Line>;
  metric: string;
  resourceType: string;
  timeSeries: Array<TimeValue>;
  transparency: number;
  unit: string;
  xScale: ScaleTime<number, number>;
  yScale: ScaleLinear<number, number>;
}

const RegularLine = ({
  filled,
  timeSeries,
  highlight,
  metric,
  lineColor,
  unit,
  lines,
  yScale,
  xScale,
  areaColor,
  transparency,
  graphHeight,
  resourceType,
}: Props): JSX.Element => {
  const openModalAD = useAtomValue(openModalADAtom);

  const strokeWidth =
    equals(metric, 'connection_lower_thresholds') ||
    equals(metric, 'connection_upper_thresholds')
      ? 0.1
      : 0.8;

  const isLegendClicked = lines?.length <= 1;
  const isHighlight = highlight || isLegendClicked ? 2 : strokeWidth;

  interface PropsIsOnline {
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
  }: PropsIsOnline): boolean => {
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

  const props = {
    curve: Curve.curveLinear,
    data: timeSeries,
    defined: (value): boolean => !isNil(value[metric]),
    opacity: highlight === false ? 0.3 : 1,
    stroke: lineColor,
    strokeWidth: isHighlight,
    unit,
    x: (timeValue): number => xScale(getTime(timeValue)) as number,
    y: (timeValue): number => yScale(prop(metric, timeValue)) ?? null,
  };

  const showCircle =
    equals(resourceType, ResourceType.anomalydetection) &&
    openModalAD &&
    !isLegendClicked;

  if (filled) {
    return (
      <Shape.AreaClosed<TimeValue>
        fill={getFillColor({ areaColor, transparency })}
        fillRule="nonzero"
        key={metric}
        y0={Math.min(yScale(0), graphHeight)}
        yScale={yScale}
        {...props}
      />
    );
  }

  return (
    <>
      {showCircle &&
        timeSeries.map((item) => {
          const metricPoint = 'connection';
          const metricPoint1 = 'connection_lower_thresholds';
          const metricPoint2 = 'connection_upper_thresholds';
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
          if (!isPointBetweenPoint1Point2) {
            return (
              <Shape.Circle
                cx={pointX}
                cy={pointY}
                fill="red"
                fillOpacity="50%"
                key={undefined}
                r={2}
              />
            );
          }

          return null;
        })}
      <Shape.LinePath<TimeValue> {...props} />;
    </>
  );
};

export default memo(RegularLine, (prevProps, nextProps) => {
  const {
    timeSeries: prevTimeSeries,
    graphHeight: prevGraphHeight,
    highlight: prevHighlight,
    xScale: prevXScale,
  } = prevProps;
  const {
    timeSeries: nextTimeSeries,
    graphHeight: nextGraphHeight,
    highlight: nextHighlight,
    xScale: nextXScale,
  } = nextProps;

  const prevXScaleRange = prevXScale.range();
  const nextXScaleRange = nextXScale.range();

  return (
    equals(prevTimeSeries, nextTimeSeries) &&
    equals(prevGraphHeight, nextGraphHeight) &&
    equals(prevHighlight, nextHighlight) &&
    equals(prevXScaleRange, nextXScaleRange)
  );
});
