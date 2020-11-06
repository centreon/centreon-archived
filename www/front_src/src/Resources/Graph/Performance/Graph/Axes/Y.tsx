import * as React from 'react';

import { scaleLinear, AxisRight, AxisLeft } from '@visx/visx';
import { isNil } from 'ramda';

import { getUnits, getValuesForUnit, getMin, getMax } from '../../timeSeries';
import formatMetricValue from '../../formatMetricValue';
import { commonTickLabelProps } from '.';
import { Line, TimeValue } from '../../models';

interface Props {
  lines: Array<Line>;
  timeSeries: Array<TimeValue>;
  graphHeight: number;
  graphWidth: number;
  base: number;
  yScale;
}

const YAxes = ({
  lines,
  timeSeries,
  graphHeight,
  graphWidth,
  base,
  leftScale,
  rightScale,
}: Props): JSX.Element => {
  const formatTick = ({ unit }) => (value): string => {
    if (isNil(value)) {
      return '';
    }

    return formatMetricValue({ value, unit, base }) as string;
  };

  const [leftUnit, rightUnit] = getUnits(lines);

  const hasMultipleYAxes = !isNil(rightUnit);

  return (
    <>
      <AxisLeft
        orientation="left"
        tickLabelProps={(): {} => ({
          ...commonTickLabelProps,
          textAnchor: 'end',
          dy: 4,
          dx: -2,
        })}
        tickFormat={formatTick({ unit: hasMultipleYAxes ? '' : leftUnit })}
        tickLength={2}
        scale={leftScale}
      />
      {hasMultipleYAxes && (
        <AxisRight
          orientation="right"
          left={graphWidth}
          tickFormat={formatTick({ unit: rightUnit })}
          tickLength={2}
          scale={rightScale}
        />
      )}
    </>
  );
};

export default YAxes;
