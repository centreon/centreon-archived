import * as React from 'react';

import { equals, isNil, map, pipe } from 'ramda';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { bisectDate } from '../../Graph';
import { TimeValue } from '../../models';

import AnchorPoint from '.';

interface StackData {
  data: TimeValue;
}

export type StackValue = [number, number, StackData];

interface Props {
  areaColor: string;
  lineColor: string;
  stackValues: Array<StackValue>;
  timeTick: Date | null;
  transparency: number;
  xScale: ScaleTime<number, number>;
  yScale: ScaleLinear<number, number>;
}

const getStackedDates = (stackValues: Array<StackValue>): Array<Date> => {
  const toTimeTick = (stackValue) => stackValue.data.timeTick;
  const toDate = (tick: string): Date => new Date(tick);

  return pipe(map(toTimeTick), map(toDate))(stackValues);
};

const getYAnchorPoint = ({
  timeTick,
  stackValues,
  yScale,
}: Pick<Props, 'timeTick' | 'stackValues' | 'yScale'>): number => {
  const index = bisectDate(getStackedDates(stackValues), timeTick);
  const timeValue = stackValues[index];
  return yScale(timeValue[1] as number);
};

const StackedAnchorPoint = ({
  xScale,
  yScale,
  stackValues,
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
    stackValues,
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
  StackedAnchorPoint,
  (prevProps, nextProps) =>
    equals(prevProps.timeTick, nextProps.timeTick) &&
    equals(prevProps.stackValues, nextProps.stackValues),
);
