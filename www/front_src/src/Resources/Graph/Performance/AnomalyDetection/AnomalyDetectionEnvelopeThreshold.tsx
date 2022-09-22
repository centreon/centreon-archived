import { equals, prop, isNil } from 'ramda';
import { Threshold } from '@visx/threshold';
import { curveBasis } from '@visx/curve';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { LinePath } from '@visx/shape';

import { useTheme } from '@mui/material/styles';

import { TimeValue, Line } from '../models';
import { getTime, getYScale } from '../timeSeries';

import AnomalyDetectionShapeCircle from './AnomalyDetectionShapeCircle';
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

interface ParamsDiff {
  factors: CustomFactorsData;
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
      stroke: theme.palette.secondary.main,
      strokeDasharray: 5,
      strokeOpacity: 0.8,
      x: getXPoint,
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
          x={getXPoint}
          y0={estimatedY0}
          y1={estimatedY1}
        />
        <LinePath {...props} y={estimatedY0} />
        <LinePath {...props} y={estimatedY1} />
        {regularLines.map(({ metric, unit, invert }) => {
          const yScale = getYScale({
            hasMoreThanTwoUnits: !isNil(thirdUnit),
            invert,
            leftScale,
            rightScale,
            secondUnit,
            unit,
          });
          const originMetric = metric.includes('_upper_thresholds')
            ? metric.replace('_upper_thresholds', '')
            : undefined;

          return (
            <g key={metric}>
              {originMetric && (
                <AnomalyDetectionShapeCircle
                  originMetric={originMetric}
                  pointXLower={getXPoint}
                  pointXOrigin={getXPoint}
                  pointXUpper={getXPoint}
                  pointYLower={estimatedY0}
                  pointYUpper={estimatedY1}
                  timeSeries={timeSeries}
                  yScale={yScale}
                />
              )}
            </g>
          );
        })}
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
      id={`${getY0Point.toString()}${getY1Point.toString()}`}
      x={getXPoint}
      y0={getY0Point}
      y1={getY1Point}
    />
  );
};

export default AnomalyDetectionEnvelopeThreshold;
