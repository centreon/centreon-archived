import * as React from 'react';

import { AxisBottom } from '@visx/visx';

import { useLocaleDateTimeFormat } from '@centreon/ui';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { Line, TimeValue } from '../../models';
import YAxes from './Y';

const commonTickLabelProps = {
  fontFamily: 'Roboto, sans-serif',
  fontSize: 10,
};

interface Props {
  timeSeries: Array<TimeValue>;
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
  timeSeries,
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

  return (
    <>
      <AxisBottom
        top={graphHeight}
        scale={xScale}
        tickFormat={formatXAxisTick}
        numTicks={7}
        tickLabelProps={(): {} => ({
          ...commonTickLabelProps,
          textAnchor: 'middle',
        })}
      />
      <YAxes
        lines={lines}
        timeSeries={timeSeries}
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
