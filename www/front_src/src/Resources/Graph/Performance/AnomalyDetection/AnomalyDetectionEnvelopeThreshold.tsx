import { equals, prop, isNil } from 'ramda';
import { Threshold } from '@visx/threshold';
import { curveBasis } from '@visx/curve';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { TimeValue, Line } from '../models';
import { getTime, getYScale } from '../timeSeries';

interface Props {
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
}: Props): JSX.Element => {
  const [
    {
      metric: metricY1,
      unit: unitY1,
      invert: invertY1,
      lineColor: lineColorY1,
    },
  ] = regularLines.filter((item) =>
    equals(item.metric, 'connection_upper_thresholds'),
  );

  const [
    {
      metric: metricY0,
      unit: unitY0,
      invert: invertY0,
      lineColor: lineColorY0,
    },
  ] = regularLines.filter((item) =>
    equals(item.metric, 'connection_lower_thresholds'),
  );

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
  const getY1Point = (timeValue): number => y1Scale(prop(metricY1, timeValue)) ?? null;
  const getY0Point = (timeValue): number => y0Scale(prop(metricY0, timeValue)) ?? null;

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
      id={`${y0.toString()}${y1.toString()}`}
      x={x}
      y0={y0}
      y1={y1}
    />
  );
};

export default AnomalyDetectionEnvelopeThreshold;
