import * as React from 'react';

import { equals, isNil, prop } from 'ramda';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { bisectDate } from '../../Graph';
import { getDates } from '../../timeSeries';
import { TimeValue } from '../../models';

import AnchorPoint from '.';

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

const RegularAnchorPoint = ({
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
    <AnchorPoint
      areaColor={areaColor}
      lineColor={lineColor}
      transparency={transparency}
      x={xAnchorPoint}
      y={yAnchorPoint}
    />
  );
};

export default React.memo(
  RegularAnchorPoint,
  (prevProps, nextProps) =>
    equals(prevProps.timeTick, nextProps.timeTick) &&
    equals(prevProps.timeSeries, nextProps.timeSeries),
);
