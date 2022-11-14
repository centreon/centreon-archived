import { curveBasis } from '@visx/curve';
import { Threshold } from '@visx/threshold';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { equals, isNil, prop } from 'ramda';

import { Line, TimeValue } from '../models';
import { getTime, getYScale } from '../timeSeries';

import AnomalyDetectionEstimatedEnvelopeThreshold from './AnomalyDetectionEstimatedEnvelopeThreshold';
import { CustomFactorsData } from './models';

interface Props {
  data?: CustomFactorsData;
  graphHeight: number;
  leftScale: ScaleLinear<number, number>;
  regularLines: Array<Line>;
  rightScale: ScaleLinear<number, number>;
  secondUnit: string;
  thirdUnit: string;
  timeSeries: Array<TimeValue>;
  xScale: ScaleTime<number, number>;
}

const AnomalyDetectionEnvelopeThreshold = ({
  secondUnit,
  regularLines,
  xScale,
  leftScale,
  rightScale,
  thirdUnit,
  timeSeries,
  graphHeight,
  data,
}: Props): JSX.Element => {
  const [
    {
      metric: metricY1,
      unit: unitY1,
      invert: invertY1,
      lineColor: lineColorY1,
    },
  ] = regularLines.filter((item) => equals(item.name, 'Upper Threshold'));

  const [
    {
      metric: metricY0,
      unit: unitY0,
      invert: invertY0,
      lineColor: lineColorY0,
    },
  ] = regularLines.filter((item) => equals(item.name, 'Lower Threshold'));

  const y1Scale = getYScale({
    hasMoreThanTwoUnits: !isNil(thirdUnit),
    invert: invertY1,
    leftScale,
    rightScale,
    secondUnit,
    unit: unitY1,
  });

  const y0Scale = getYScale({
    hasMoreThanTwoUnits: !isNil(thirdUnit),
    invert: invertY0,
    leftScale,
    rightScale,
    secondUnit,
    unit: unitY0,
  });

  const getXPoint = (timeValue): number => xScale(getTime(timeValue)) as number;
  const getY1Point = (timeValue): number =>
    y1Scale(prop(metricY1, timeValue)) ?? null;
  const getY0Point = (timeValue): number =>
    y0Scale(prop(metricY0, timeValue)) ?? null;

  const props = {
    getXPoint,
    graphHeight,
    leftScale,
    metricY0,
    metricY1,
    regularLines,
    rightScale,
    secondUnit,
    thirdUnit,
    timeSeries,
    y0Scale,
    y1Scale,
  };

  if (data) {
    return (
      <AnomalyDetectionEstimatedEnvelopeThreshold {...props} data={data} />
    );
  }

  return (
    <Threshold
      aboveAreaProps={{
        fill: lineColorY1,
        fillOpacity: 0.1,
      }}
      belowAreaProps={{
        fill: lineColorY0,
        fillOpacity: 0.1,
      }}
      clipAboveTo={0}
      clipBelowTo={graphHeight}
      curve={curveBasis}
      data={timeSeries}
      id={`${getY0Point.toString()}${getY1Point.toString()}`}
      x={getXPoint}
      y0={getY0Point}
      y1={getY1Point}
    />
  );
};

export default AnomalyDetectionEnvelopeThreshold;
