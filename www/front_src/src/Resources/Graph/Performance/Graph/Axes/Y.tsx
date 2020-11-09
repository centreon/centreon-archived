import * as React from 'react';

import { AxisRight, AxisLeft } from '@visx/visx';
import { isNil } from 'ramda';
import { ScaleLinear } from 'd3-scale';

import { getUnits } from '../../timeSeries';
import formatMetricValue from '../../formatMetricValue';
import { commonTickLabelProps } from '.';
import { Line, TimeValue } from '../../models';

interface Props {
  lines: Array<Line>;
  timeSeries: Array<TimeValue>;
  graphWidth: number;
  base: number;
  leftScale: ScaleLinear<number, number>;
  rightScale: ScaleLinear<number, number>;
}

interface UnitLabelProps {
  y: number;
  unit: string;
}

const UnitLabel = ({ y, unit }: UnitLabelProps): JSX.Element => {
  return (
    <text
      x={-15}
      y={y}
      transform="rotate(-90)"
      fontSize={commonTickLabelProps.fontSize}
      fontFamily={commonTickLabelProps.fontFamily}
    >
      {unit}
    </text>
  );
};

const YAxes = ({
  lines,
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

  const [firstUnit, secondUnit, thirdUnit] = getUnits(lines);

  const hasMoreThanTwoUnits = !isNil(thirdUnit);
  const hasTwoUnits = !isNil(secondUnit) && !hasMoreThanTwoUnits;

  return (
    <>
      {!hasMoreThanTwoUnits && <UnitLabel y={15} unit={firstUnit} />}
      <AxisLeft
        orientation="left"
        tickLabelProps={(): {} => ({
          ...commonTickLabelProps,
          textAnchor: 'end',
          dy: 4,
          dx: -2,
        })}
        tickFormat={formatTick({ unit: hasMoreThanTwoUnits ? '' : firstUnit })}
        tickLength={2}
        scale={leftScale}
      />
      {hasTwoUnits && (
        <>
          <AxisRight
            orientation="right"
            left={graphWidth}
            tickFormat={formatTick({ unit: secondUnit })}
            tickLength={2}
            scale={rightScale}
          />
          <UnitLabel unit={secondUnit} y={graphWidth - 5} />
        </>
      )}
    </>
  );
};

export default YAxes;
