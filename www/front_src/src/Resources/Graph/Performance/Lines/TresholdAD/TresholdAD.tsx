import { equals, prop, isNil } from 'ramda';
import { Threshold } from '@visx/threshold';
import { curveBasis } from '@visx/curve';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { TimeValue } from '../../models';
import { getTime } from '../../timeSeries';

interface Props {
  getYScale;
  graphHeight: number;
  leftScale: ScaleLinear<number, number>;
  regularLines;
  rightScale: ScaleLinear<number, number>;
  secondUnit: string;
  thirdUnit: string;
  timeSeries: Array<TimeValue>;
  xScale: ScaleTime<number, number>;
}

const TresholdAD = ({
  secondUnit,
  regularLines,
  xScale,
  getYScale,
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

  const X = (timeValue): number => xScale(getTime(timeValue)) as number;
  const Y1 = (timeValue): number => y1Scale(prop(metricY1, timeValue)) ?? null;
  const Y0 = (timeValue): number => y0Scale(prop(metricY0, timeValue)) ?? null;

  return (
    <Threshold
      aboveAreaProps={{
        fill: lineColorY1,
        fillOpacity: 0.2,
      }}
      belowAreaProps={{
        fill: lineColorY0,
        fillOpacity: 0.2,
      }}
      clipAboveTo={0}
      clipBelowTo={graphHeight}
      curve={curveBasis}
      data={timeSeries}
      id={`${Math.random()}`}
      x={X}
      y0={Y0}
      y1={Y1}
    />
  );
};

export default TresholdAD;
