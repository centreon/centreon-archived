import * as React from 'react';

import { Axis } from '@visx/visx';
import { isNil } from 'ramda';
import { ScaleLinear } from 'd3-scale';

import { getUnits } from '../../timeSeries';
import formatMetricValue from '../../formatMetricValue';
import { Line } from '../../models';

import { commonTickLabelProps } from '.';

interface Props {
  base: number;
  graphHeight: number;
  graphWidth: number;
  leftScale: ScaleLinear<number, number>;
  lines: Array<Line>;
  rightScale: ScaleLinear<number, number>;
}

interface UnitLabelProps {
  unit: string;
  x: number;
}

const UnitLabel = ({ x, unit }: UnitLabelProps): JSX.Element => (
  <text
    fontFamily={commonTickLabelProps.fontFamily}
    fontSize={commonTickLabelProps.fontSize}
    x={x}
    y={-8}
  >
    {unit}
  </text>
);

const YAxes = ({
  lines,
  graphWidth,
  base,
  leftScale,
  rightScale,
  graphHeight,
}: Props): JSX.Element => {
  const formatTick =
    ({ unit }) =>
    (value): string => {
      if (isNil(value)) {
        return '';
      }

      return formatMetricValue({ base, unit, value }) as string;
    };

  const [firstUnit, secondUnit, thirdUnit] = getUnits(lines);

  const hasMoreThanTwoUnits = !isNil(thirdUnit);
  const hasTwoUnits = !isNil(secondUnit) && !hasMoreThanTwoUnits;

  const ticksCount = Math.ceil(graphHeight / 30);

  return (
    <>
      {!hasMoreThanTwoUnits && <UnitLabel unit={firstUnit} x={0} />}
      <Axis.AxisLeft
        numTicks={ticksCount}
        orientation="left"
        scale={leftScale}
        tickFormat={formatTick({ unit: hasMoreThanTwoUnits ? '' : firstUnit })}
        tickLabelProps={(): Record<string, unknown> => ({
          ...commonTickLabelProps,
          dx: -2,
          dy: 4,
          textAnchor: 'end',
        })}
        tickLength={2}
      />
      {hasTwoUnits && (
        <>
          <Axis.AxisRight
            left={graphWidth}
            numTicks={ticksCount}
            orientation="right"
            scale={rightScale}
            tickFormat={formatTick({ unit: secondUnit })}
            tickLength={2}
          />
          <UnitLabel unit={secondUnit} x={graphWidth} />
        </>
      )}
    </>
  );
};

export default YAxes;
