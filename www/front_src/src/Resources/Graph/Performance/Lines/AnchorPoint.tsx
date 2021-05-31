import * as React from 'react';

import { equals, isNil, prop } from 'ramda';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { bisectDate } from '../Graph';
import { getDates } from '../timeSeries';
import { TimeValue } from '../models';

interface Props {
  areaColor: string;
  lineColor: string;
  metric: string;
  timeSeries: Array<TimeValue>;
  timeTick: Date | null;
  transparency: number;
  xScale: ScaleTime<number, number>;
  yScale: ScaleLinear<number, number>;
}

const getYAnchorPoint = ({
  timeTick,
  timeSeries,
  yScale,
  metric,
}: Pick<Props, 'timeTick' | 'timeSeries' | 'yScale' | 'metric'>): number => {
  const index = bisectDate(getDates(timeSeries), timeTick);
  const timeValue = timeSeries[index];
  return yScale(prop(metric, timeValue) as number);
};

const AnchorPoint = ({
  xScale,
  yScale,
  metric,
  timeSeries,
  timeTick,
  areaColor,
  transparency,
  lineColor,
}: Props): JSX.Element | null => {
  if (isNil(timeTick)) {
    return null;
  }
  const xAnchorPoint = xScale(timeTick);

  const yAnchorPoint = getYAnchorPoint({
    metric,
    timeSeries,
    timeTick,
    yScale,
  });

  return (
    <circle
      cx={xAnchorPoint}
      cy={yAnchorPoint}
      fill={areaColor}
      fillOpacity={1 - transparency * 0.01}
      r={3}
      stroke={lineColor}
    />
  );
};

export default React.memo(
  AnchorPoint,
  (prevProps, nextProps) =>
    equals(prevProps.timeTick, nextProps.timeTick) &&
    equals(prevProps.timeSeries, nextProps.timeSeries),
);
