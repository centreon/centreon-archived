import { Axis } from '@visx/visx';
import { ScaleLinear, ScaleTime } from 'd3-scale';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import { Line } from '../../models';

import YAxes from './Y';

const commonTickLabelProps = {
  fontFamily: 'Roboto, sans-serif',
  fontSize: 10,
};

interface Props {
  base: number;
  graphHeight: number;
  graphWidth: number;
  isEditAnomalyDetectionDataDialogOpen?: boolean;
  leftScale: ScaleLinear<number, number>;
  lines: Array<Line>;
  rightScale: ScaleLinear<number, number>;
  xAxisTickFormat: string;
  xScale: ScaleTime<number, number>;
}

const Axes = ({
  lines,
  graphHeight,
  graphWidth,
  leftScale,
  rightScale,
  xScale,
  xAxisTickFormat,
  isEditAnomalyDetectionDataDialogOpen,
  base,
}: Props): JSX.Element => {
  const { format } = useLocaleDateTimeFormat();

  const formatXAxisTick = (tick): string =>
    format({ date: new Date(tick), formatString: xAxisTickFormat });

  const xTickCount = Math.ceil(graphWidth / 82);

  const xTickCountEditAnomalyDetectionDataDialog =
    xTickCount > 10 ? 10 : xTickCount;

  return (
    <>
      <Axis.AxisBottom
        numTicks={
          isEditAnomalyDetectionDataDialogOpen
            ? xTickCountEditAnomalyDetectionDataDialog
            : xTickCount
        }
        scale={xScale}
        tickFormat={formatXAxisTick}
        tickLabelProps={(): Record<string, unknown> => ({
          ...commonTickLabelProps,
          textAnchor: 'middle',
        })}
        top={graphHeight}
      />
      <YAxes
        base={base}
        graphHeight={graphHeight}
        graphWidth={graphWidth}
        leftScale={leftScale}
        lines={lines}
        rightScale={rightScale}
      />
    </>
  );
};

export default Axes;
export { commonTickLabelProps };
