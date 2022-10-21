import { ReactNode } from 'react';

import { ResourceDetails } from '../../../Details/models';
import ExportablePerformanceGraphWithTimeline from '../ExportableGraphWithTimeline/index';

import AdditionalLines from './AnomalyDetectionAdditionalLines';
import AnomalyDetectionSlider from './AnomalyDetectionSlider';
import EditAnomalyDetectionDataDialog from './EditAnomalyDetectionDataDialog';

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
      <ExportablePerformanceGraphWithTimeline
        additionalData={factorsData}
        graphHeight={180}
        interactWithGraph={false}
        isRenderAdditionalGraphActions={false}
        renderAdditionalLines={({ additionalLinesProps }): JSX.Element => (
          <AdditionalLines
            additionalLinesProps={additionalLinesProps}
            data={factorsData}
          />
        )}
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
