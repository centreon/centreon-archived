import { ReactNode } from 'react';

import { ResourceDetails } from '../../../Details/models';
import ExportablePerformanceGraphWithTimeline from '../ExportableGraphWithTimeline/index';

import { getDisplayAdditionalLinesConditionForGraphActions } from './AnomalyDetectionAdditionalLines';
import AnomalyDetectionSlider from './AnomalyDetectionSlider';
import EditAnomalyDetectionDataDialog from './EditAnomalyDetectionDataDialog';
import { CustomFactorsData } from './models';

interface AdditionalGraphActionsProps {
  details: ResourceDetails | undefined;
  sendReloadGraphPerformance: (value: boolean) => void;
}

const AnomalyDetectionGraphActions = ({
  details,
  sendReloadGraphPerformance,
}: AdditionalGraphActionsProps): JSX.Element => (
  <EditAnomalyDetectionDataDialog
    renderGraph={({ factorsData }): JSX.Element => (
      <ExportablePerformanceGraphWithTimeline<CustomFactorsData>
        additionalData={factorsData}
        getDisplayAdditionalLinesCondition={getDisplayAdditionalLinesConditionForGraphActions(
          factorsData,
        )}
        graphHeight={180}
        interactWithGraph={false}
        isRenderAdditionalGraphActions={false}
        resource={details}
      />
    )}
    renderSlider={({
      getFactors,
      openModalConfirmation,
      isEnvelopeResizingCanceled,
      isResizingEnvelope,
      setIsResizingEnvelope,
    }): ReactNode =>
      details?.sensitivity && (
        <AnomalyDetectionSlider
          details={details}
          isEnvelopeResizingCanceled={isEnvelopeResizingCanceled}
          isResizingEnvelope={isResizingEnvelope}
          openModalConfirmation={openModalConfirmation}
          sendFactors={getFactors}
          sendReloadGraphPerformance={sendReloadGraphPerformance}
          sensitivity={details?.sensitivity}
          setIsResizingEnvelope={setIsResizingEnvelope}
        />
      )
    }
  />
);

export default AnomalyDetectionGraphActions;
