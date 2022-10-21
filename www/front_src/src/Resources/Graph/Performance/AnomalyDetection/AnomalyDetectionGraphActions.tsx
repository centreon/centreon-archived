import { ReactNode } from 'react';

import { ResourceDetails } from '../../../Details/models';
import { Resource } from '../../../models';
import ExportablePerformanceGraphWithTimeline from '../ExportableGraphWithTimeline/index';

import AnomalyDetectionSlider from './AnomalyDetectionSlider';
import EditAnomalyDetectionDataDialog from './EditAnomalyDetectionDataDialog';

interface AdditionalGraphActionsProps {
  details: ResourceDetails | undefined;
  resource: ResourceDetails | Resource | undefined;
  sendReloadGraphPerformance: (value: boolean) => void;
}

const AnomalyDetectionGraphActions = ({
  resource,
  details,
  sendReloadGraphPerformance,
}: AdditionalGraphActionsProps): JSX.Element => (
  <EditAnomalyDetectionDataDialog
    renderGraph={({ factorsData }): JSX.Element => (
      <ExportablePerformanceGraphWithTimeline
        additionalData={factorsData}
        graphHeight={180}
        interactWithGraph={false}
        isRenderAdditionalGraphActions={false}
        resource={resource}
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
