import { memo } from 'react';

import { Curve, Shape } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { equals, isNil, prop } from 'ramda';

import { ResourceType } from '../../../models';
import ShapeCircleAnomalyDetection from '../AnomalyDetection/AnomalyDetectionShapeCircle';
import { Line, TimeValue } from '../models';
import { getTime } from '../timeSeries';

import { getFillColor } from '.';

interface Props {
  areaColor: string;
  filled: boolean;
  graphHeight: number;
  highlight?: boolean;
  isEditAnomalyDetectionDataDialogOpen?: boolean;
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
  isEditAnomalyDetectionDataDialogOpen,
}: Props): JSX.Element => {
  const strokeWidth =
    equals(metric, 'connection_lower_thresholds') ||
    equals(metric, 'connection_upper_thresholds')
      ? 0.1
      : 0.8;

  const isLegendClicked = lines?.length <= 1;
  const isHighlighted = highlight || isLegendClicked ? 2 : strokeWidth;
  const showCircle =
    equals(resourceType, ResourceType.anomalydetection) &&
    isEditAnomalyDetectionDataDialogOpen &&
    !isLegendClicked;

  const props = {
    curve: Curve.curveLinear,
    data: timeSeries,
    defined: (value): boolean => !isNil(value[metric]),
    opacity: highlight === false ? 0.3 : 1,
    stroke: lineColor,
    strokeWidth: isHighlighted,
    unit,
    x: (timeValue): number => xScale(getTime(timeValue)) as number,
    y: (timeValue): number => yScale(prop(metric, timeValue)) ?? null,
  };

  const propsShapeCircle = {
    getTime,
    timeSeries,
    xScale,
    yScale,
  };

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
      {showCircle && <ShapeCircleAnomalyDetection {...propsShapeCircle} />}
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
