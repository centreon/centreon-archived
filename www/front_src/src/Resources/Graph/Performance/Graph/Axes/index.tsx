import * as React from 'react';

import { AxisBottom } from '@visx/visx';

import { useLocaleDateTimeFormat } from '@centreon/ui';
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
  xScale;
  yScale;
  xAxisTickFormat: string;
  base: number;
}

const Axes = ({
  timeSeries,
  lines,
  graphHeight,
  graphWidth,
  xScale,
  yScale,
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
        graphHeight={graphHeight}
        graphWidth={graphWidth}
        base={base}
        yScale={yScale}
      />
    </>
  );
};

export default Axes;
export { commonTickLabelProps };
