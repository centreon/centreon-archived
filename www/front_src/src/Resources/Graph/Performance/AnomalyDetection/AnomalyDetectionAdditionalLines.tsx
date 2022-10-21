import { ScaleLinear, ScaleTime } from 'd3-scale';

import { Line, TimeValue } from '../models';

import AnomalyDetectionEnvelopeThreshold from './AnomalyDetectionEnvelopeThreshold';
import { CustomFactorsData } from './models';

interface LinesProps {
  displayAdditionalLines: boolean;
  getTime: (timeValue: TimeValue) => number;
  graphHeight: number;
  leftScale: ScaleLinear<number, number, never>;
  lines: Array<Line>;
  regularLines: Array<Line>;
  rightScale: ScaleLinear<number, number, never>;
  secondUnit: string;
  thirdUnit: string;
  timeSeries: Array<TimeValue>;
  xScale: ScaleTime<number, number, never>;
}

interface AdditionalLinesProps {
  additionalLinesProps: LinesProps;
  data: CustomFactorsData | null | undefined;
}
const AdditionalLines = ({
  additionalLinesProps,
  data,
}: AdditionalLinesProps): JSX.Element => (
  <>
    <AnomalyDetectionEnvelopeThreshold {...additionalLinesProps} />
    <AnomalyDetectionEnvelopeThreshold {...additionalLinesProps} data={data} />
  </>
);

export default AdditionalLines;
