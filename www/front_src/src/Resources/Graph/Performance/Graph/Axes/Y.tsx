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
  yScale,
}: Props): JSX.Element => {
  const [leftUnit, rightUnit] = getUnits(lines);

  const leftUnitScale = React.useMemo(
    () =>
      scaleLinear<number>({
        domain: [
          getMin(getValuesForUnit({ lines, timeSeries, unit: leftUnit })) ?? 0,
          getMax(getValuesForUnit({ lines, timeSeries, unit: leftUnit })) ?? 0,
        ],
        nice: true,
        range: [graphHeight, 0],
      }),
    [timeSeries, lines, leftUnit, graphHeight],
  );

  const rightUnitScale = React.useMemo(
    () =>
      scaleLinear<number>({
        domain: [
          getMin(getValuesForUnit({ lines, timeSeries, unit: rightUnit })),
          getMax(getValuesForUnit({ lines, timeSeries, unit: rightUnit })),
        ],
        nice: true,
        range: [graphHeight, 0],
      }),
    [timeSeries, lines, rightUnit, graphHeight],
  );

  const formatTick = ({ unit }) => (value): string => {
    if (isNil(value)) {
      return '';
    }

    return formatMetricValue({ value, unit, base }) as string;
  };

  const multipleYAxes = !isNil(rightUnit);

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
        tickFormat={formatTick({ unit: multipleYAxes ? leftUnit : '' })}
        scale={multipleYAxes ? leftUnitScale : yScale}
      />
      {multipleYAxes && (
        <AxisRight
          orientation="right"
          left={graphWidth}
          tickFormat={formatTick({ unit: rightUnit })}
          scale={rightUnitScale}
        />
      )}
    </>
  );
};

export default YAxes;
