import { ScaleLinear, ScaleTime } from 'd3-scale';
import { useAtomValue } from 'jotai/utils';
import { equals, isNil } from 'ramda';

import { detailsAtom } from '../../../../../Details/detailsAtoms';
import { ResourceDetails } from '../../../../../Details/models';
import { Resource, ResourceType } from '../../../../../models';
import {
  GetDisplayAdditionalLinesConditionProps,
  Line,
  TimeValue,
} from '../../../models';
import { thresholdsAnomalyDetectionDataAtom } from '../../anomalyDetectionAtom';
import { CustomFactorsData } from '../../models';

import AnomalyDetectionEnvelopeThreshold from './AnomalyDetectionEnvelopeThreshold';
import AnomalyDetectionExclusionPeriodsThreshold from './AnomalyDetectionExclusionPeriodsThreshold';
import { displayAdditionalLines } from './helpers';

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
  displayThresholdPeriod?: boolean;
}
const AdditionalLines = ({
  additionalLinesProps,
  data,
  displayThresholdPeriod = true,
}: AdditionalLinesProps): JSX.Element => {
  const details = useAtomValue(detailsAtom);

  const { exclusionPeriodsThreshold } = useAtomValue(
    thresholdsAnomalyDetectionDataAtom,
  );

  const { graphHeight, graphWidth, lines, xScale, leftScale, rightScale } =
    additionalLinesProps;

  const isDisplayedThresholds = displayAdditionalLines({
    lines,
    resource: details as ResourceDetails,
  });

  return (
    <>
      {isDisplayedThresholds && (
        <>
          <AnomalyDetectionEnvelopeThreshold {...additionalLinesProps} />
          <AnomalyDetectionEnvelopeThreshold
            {...additionalLinesProps}
            data={data}
          />
        </>
      )}
      {displayThresholdPeriod &&
        exclusionPeriodsThreshold?.data?.map((item, index) => {
          const displayed =
            item.lines?.length > 0 && item.timeSeries?.length > 0;

          return (
            displayed && (
              <AnomalyDetectionExclusionPeriodsThreshold
                data={item}
                graphHeight={graphHeight}
                graphWidth={graphWidth}
                key={index}
                leftScale={leftScale}
                resource={details}
                rightScale={rightScale}
                xScale={xScale}
              />
            )
          );
        })}
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
      displayThresholdPeriod={false}
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
