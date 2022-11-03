import { ScaleLinear, ScaleTime } from 'd3-scale';
import { equals } from 'ramda';

import { ResourceDetails } from '../../../../../Details/models';
import { Resource, ResourceType } from '../../../../../models';
import {
  GetDisplayAdditionalLinesConditionProps,
  Line,
  TimeValue,
} from '../../../models';
import AnomalyDetectionExclusionPeriod from '../../exclusionPeriods';
import { CustomFactorsData } from '../../models';

import AnomalyDetectionEnvelopeThreshold from './AnomalyDetectionEnvelopeThreshold';

interface LinesProps {
  displayAdditionalLines: boolean;
  getTime: (timeValue: TimeValue) => number;
  graphHeight: number;
  graphWidth: number;
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
  dataTest?: {
    estimatedEnvelopeSize?: { data: CustomFactorsData | null | undefined };
    exclusionPeriods?: { data: { lines: any; timeSeries: any } };
  };
}
const AdditionalLines = ({
  additionalLinesProps,
  data,
  dataTest,
}: AdditionalLinesProps): JSX.Element => {
  return (
    <>
      <AnomalyDetectionEnvelopeThreshold {...additionalLinesProps} />
      <AnomalyDetectionEnvelopeThreshold
        {...additionalLinesProps}
        data={data}
      />
      <AnomalyDetectionExclusionPeriod data={dataTest} />
    </>
  );
};

export const getDisplayAdditionalLinesCondition = {
  condition: (resource: Resource | ResourceDetails): boolean =>
    equals(resource.type, ResourceType.anomalyDetection),
  displayAdditionalLines: ({
    additionalData,
    additionalLinesProps,
  }): JSX.Element => (
    <AdditionalLines
      additionalLinesProps={additionalLinesProps}
      data={additionalData}
    />
  ),
};

export const getDisplayAdditionalLinesConditionForGraphActions = (
  factorsData?: CustomFactorsData | null,
): GetDisplayAdditionalLinesConditionProps => ({
  condition: (resource: Resource | ResourceDetails): boolean =>
    equals(resource.type, ResourceType.anomalyDetection),
  displayAdditionalLines: ({ additionalLinesProps }): JSX.Element => (
    <AdditionalLines
      additionalLinesProps={additionalLinesProps}
      data={factorsData}
    />
  ),
});
