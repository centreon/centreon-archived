import { curveBasis } from '@visx/curve';
import { LinePath } from '@visx/shape';
import { Threshold } from '@visx/threshold';
import { ScaleLinear } from 'd3-scale';
import { equals, isNil, prop } from 'ramda';

import { useTheme } from '@mui/material/styles';

import { Line, TimeValue } from '../models';
import { getYScale } from '../timeSeries';

import AnomalyDetectionShapeCircle from './AnomalyDetectionShapeCircle';
import { CustomFactorsData } from './models';

interface EnvelopeVariation {
  factors: CustomFactorsData;
  item: TimeValue;
  metricLower: string;
  metricUpper: string;
}

interface Props {
  data: CustomFactorsData;
  getXPoint;
  graphHeight: number;
  leftScale: ScaleLinear<number, number>;
  metricY0: string;
  metricY1: string;
  regularLines: Array<Line>;
  rightScale: ScaleLinear<number, number>;
  secondUnit: string;
  thirdUnit: string;
  timeSeries: Array<TimeValue>;
  y0Scale: ScaleLinear<number, number>;
  y1Scale: ScaleLinear<number, number>;
}

const AnomalyDetectionEstimatedEnvelopeThreshold = ({
  data,
  getXPoint,
  metricY0,
  metricY1,
  regularLines,
  y0Scale,
  y1Scale,
  timeSeries,
  leftScale,
  rightScale,
  thirdUnit,
  graphHeight,
  secondUnit,
}: Props): JSX.Element => {
  const theme = useTheme();

  const envelopeVariation = ({
    metricUpper,
    metricLower,
    item,
    factors,
  }: EnvelopeVariation): number => {
    return (
      ((prop(metricUpper, item) as number) -
        (prop(metricLower, item) as number)) *
      (1 - factors.simulatedFactor / factors.currentFactor)
    );
  };

  const estimatedY1 = (timeValue): number => {
    const diff = envelopeVariation({
      factors: data,
      item: timeValue,
      metricLower: metricY0,
      metricUpper: metricY1,
    });

    return y1Scale(prop(metricY1, timeValue) - diff) ?? null;
  };

  const estimatedY0 = (timeValue): number => {
    const diff = envelopeVariation({
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
      {regularLines.map(({ metric, unit, invert, name }) => {
        const yScale = getYScale({
          hasMoreThanTwoUnits: !isNil(thirdUnit),
          invert,
          leftScale,
          rightScale,
          secondUnit,
          unit,
        });
        const originMetric =
          !equals(name, 'Upper Threshold') && !equals(name, 'Lower Threshold')
            ? metric
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
};

export default AnomalyDetectionEstimatedEnvelopeThreshold;
