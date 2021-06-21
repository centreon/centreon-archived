import * as React from 'react';

import { AreaClosed, curveLinear, LinePath } from '@visx/visx';
import { equals, isNil, prop } from 'ramda';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { getTime } from '../timeSeries';
import { TimeValue } from '../models';

import { getFillColor } from '.';

interface Props {
  areaColor: string;
  filled: boolean;
  graphHeight: number;
  highlight?: boolean;
  lineColor: string;
  metric: string;
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
  yScale,
  xScale,
  areaColor,
  transparency,
  graphHeight,
}: Props): JSX.Element => {
  const props = {
    curve: curveLinear,
    data: timeSeries,
    defined: (value): boolean => !isNil(value[metric]),
    opacity: highlight === false ? 0.3 : 1,
    stroke: lineColor,
    strokeWidth: highlight ? 2 : 1,
    unit,
    x: (timeValue): number => xScale(getTime(timeValue)) as number,
    y: (timeValue): number => yScale(prop(metric, timeValue)) ?? null,
  };

  if (filled) {
    return (
      <AreaClosed<TimeValue>
        fill={getFillColor({ areaColor, transparency })}
        fillRule="nonzero"
        key={metric}
        y0={Math.min(yScale(0), graphHeight)}
        yScale={yScale}
        {...props}
      />
    );
  }

  return <LinePath<TimeValue> {...props} />;
};

export default React.memo(RegularLine, (prevProps, nextProps) => {
  const {
    timeSeries: prevTimeSeries,
    graphHeight: prevGraphHeight,
    highlight: prevHighlight,
  } = prevProps;
  const {
    timeSeries: nextTimeSeries,
    graphHeight: nextGraphHeight,
    highlight: nextHighlight,
  } = nextProps;

  return (
    equals(prevTimeSeries, nextTimeSeries) &&
    equals(prevGraphHeight, nextGraphHeight) &&
    equals(prevHighlight, nextHighlight)
  );
});
