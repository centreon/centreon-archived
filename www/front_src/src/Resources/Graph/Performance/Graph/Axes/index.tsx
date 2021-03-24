import * as React from 'react';

import { AxisBottom } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import { Line } from '../../models';

import YAxes from './Y';

const commonTickLabelProps = {
  fontFamily: 'Roboto, sans-serif',
  fontSize: 10,
};

interface Props {
  lines: Array<Line>;
  graphHeight: number;
  graphWidth: number;
  leftScale: ScaleLinear<number, number>;
  rightScale: ScaleLinear<number, number>;
  xScale: ScaleTime<number, number>;
  xAxisTickFormat: string;
  base: number;
}

const Axes = ({
  lines,
  graphHeight,
  graphWidth,
  leftScale,
  rightScale,
  xScale,
  xAxisTickFormat,
  base,
}: Props): JSX.Element => {
  const { format } = useLocaleDateTimeFormat();

  const formatXAxisTick = (tick): string =>
    format({ date: new Date(tick), formatString: xAxisTickFormat });

  const xTickCount = Math.ceil(graphWidth / 82);

  return (
    <>
      <AxisBottom
        top={graphHeight}
        scale={xScale}
        tickFormat={formatXAxisTick}
        numTicks={xTickCount}
        tickLabelProps={(): Record<string, unknown> => ({
          ...commonTickLabelProps,
          textAnchor: 'middle',
        })}
      />
      <YAxes
        lines={lines}
        graphWidth={graphWidth}
        graphHeight={graphHeight}
        base={base}
        leftScale={leftScale}
        rightScale={rightScale}
      />
    </>
  );
};

export default Axes;
export { commonTickLabelProps };
