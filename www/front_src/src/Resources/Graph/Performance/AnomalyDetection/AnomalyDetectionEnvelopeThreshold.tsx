import { equals, prop, isNil } from 'ramda';
import { Threshold } from '@visx/threshold';
import { curveBasis } from '@visx/curve';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { LinePath } from '@visx/shape';

import { useTheme } from '@mui/material/styles';

import { TimeValue, Line } from '../models';
import { getTime, getYScale } from '../timeSeries';

import { FactorsData } from './models';

interface Props {
  data?: FactorsData;
  graphHeight: number;
  leftScale: ScaleLinear<number, number>;
  regularLines: Array<Line>;
  rightScale: ScaleLinear<number, number>;
  secondUnit: string;
  thirdUnit: string;
  timeSeries: Array<TimeValue>;
  xScale: ScaleTime<number, number>;
}

interface ParamsDiff {
  factors: FactorsData;
  item: TimeValue;
  metricLower: string;
  metricUpper: string;
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
  const theme = useTheme();

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

  const x = (timeValue): number => xScale(getTime(timeValue)) as number;
  const y1 = (timeValue): number => y1Scale(prop(metricY1, timeValue)) ?? null;
  const y0 = (timeValue): number => y0Scale(prop(metricY0, timeValue)) ?? null;

  if (data) {
    const getDiff = ({
      metricUpper,
      metricLower,
      item,
      factors,
    }: ParamsDiff): number => {
      return (
        ((prop(metricUpper, item) as number) -
          (prop(metricLower, item) as number)) *
        (1 - factors.simulatedFactor / factors.currentFactor)
      );
    };

    const estimatedY1 = (timeValue): number => {
      const diff = getDiff({
        factors: data,
        item: timeValue,
        metricLower: metricY0,
        metricUpper: metricY1,
      });

      return y1Scale(prop(metricY1, timeValue) - diff) ?? null;
    };

    const estimatedY0 = (timeValue): number => {
      const diff = getDiff({
        factors: data,
        item: timeValue,
        metricLower: metricY0,
        metricUpper: metricY1,
      });

      return y0Scale(prop(metricY0, timeValue) + diff) ?? null;
    };

    const props = {
      curve: curveBasis,
      data: timeSeries,
      stroke: theme.palette.primary.main,
      strokeDasharray: 5,
      strokeOpacity: 0.8,
      x,
    };

    return (
      <>
        <Threshold
          aboveAreaProps={{
            fill: theme.palette.secondary.main,
            fillOpacity: 0.1,
          }}
          belowAreaProps={{
            fill: theme.palette.secondary.main,
            fillOpacity: 0.1,
          }}
          clipAboveTo={0}
          clipBelowTo={graphHeight}
          curve={curveBasis}
          data={timeSeries}
          id={`${estimatedY0.toString()}${estimatedY1.toString()}`}
          x={x}
          y0={estimatedY0}
          y1={estimatedY1}
        />
        <LinePath {...props} y={estimatedY0} />
        <LinePath {...props} y={estimatedY1} />
      </>
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
      id={`${y0.toString()}${y1.toString()}`}
      x={x}
      y0={y0}
      y1={y1}
    />
  );
};

export default AnomalyDetectionEnvelopeThreshold;
