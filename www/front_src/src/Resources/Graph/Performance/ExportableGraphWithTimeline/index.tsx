import { MutableRefObject, useEffect, useMemo, useRef, useState } from 'react';

import { path, isNil, or, not } from 'ramda';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import { Paper, Theme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { useRequest, ListingModel } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { CustomFactorsData } from '../AnomalyDetection/models';
import { TimelineEvent } from '../../../Details/tabs/Timeline/models';
import { listTimelineEvents } from '../../../Details/tabs/Timeline/api';
import { listTimelineEventsDecoder } from '../../../Details/tabs/Timeline/api/decoders';
import PerformanceGraph from '..';
import { Resource } from '../../../models';
import { ResourceDetails } from '../../../Details/models';
import { GraphOptionId } from '../models';
import { useIntersection } from '../useGraphIntersection';
import MemoizedGraphActions from '../GraphActions';
import {
  adjustTimePeriodDerivedAtom,
  customTimePeriodAtom,
  getDatesDerivedAtom,
  graphQueryParametersDerivedAtom,
  resourceDetailsUpdatedAtom,
  selectedTimePeriodAtom,
} from '../TimePeriods/timePeriodAtoms';
import { detailsAtom } from '../../../Details/detailsAtoms';
import EditAnomalyDetectionDataDialog from '../AnomalyDetection/EditAnomalyDetectionDataDialog';

import { graphOptionsAtom } from './graphOptionsAtoms';

const useStyles = makeStyles((theme: Theme) => ({
  graph: {
    height: '100%',
    margin: 'auto',
    width: '100%',
  },
  graphContainer: {
    display: 'grid',
    gridTemplateRows: '1fr',
    height: '93%',
    padding: theme.spacing(2, 1, 1),
  },
}));

interface Props {
  graphHeight: number;
  isEditAnomalyDetectionDataDialogOpen: boolean;
  limitLegendRows?: boolean;
  onReload?: (value: boolean) => void;
  resizeEnvelopeData?: CustomFactorsData;
  resource?: Resource | ResourceDetails;
}

const ExportablePerformanceGraphWithTimeline = ({
  resource,
  graphHeight,
  limitLegendRows,
  isEditAnomalyDetectionDataDialogOpen,
  resizeEnvelopeData,
  onReload,
}: Props): JSX.Element => {
  const classes = useStyles();
  const [timeline, setTimeline] = useState<Array<TimelineEvent>>();
  const [performanceGraphRef, setPerformanceGraphRef] =
    useState<HTMLDivElement | null>(null);
  const [isOpenModalAD, setIsOpenModalAD] = useState(false);

  const { sendRequest: sendGetTimelineRequest } = useRequest<
    ListingModel<TimelineEvent>
  >({
    decoder: listTimelineEventsDecoder,
    request: listTimelineEvents,
  });

  const { alias } = useAtomValue(userAtom);
  const graphOptions = useAtomValue(graphOptionsAtom);
  const getGraphQueryParameters = useAtomValue(graphQueryParametersDerivedAtom);
  const selectedTimePeriod = useAtomValue(selectedTimePeriodAtom);
  const customTimePeriod = useAtomValue(customTimePeriodAtom);
  const resourceDetailsUpdated = useAtomValue(resourceDetailsUpdatedAtom);
  const getIntervalDates = useAtomValue(getDatesDerivedAtom);
  const details = useAtomValue(detailsAtom);
  const adjustTimePeriod = useUpdateAtom(adjustTimePeriodDerivedAtom);

  const graphContainerRef = useRef<HTMLElement | null>(null);

  const { setElement, isInViewport } = useIntersection();

  const displayEventAnnotations = path<boolean>(
    [GraphOptionId.displayEvents, 'value'],
    graphOptions,
  );

  const endpoint = path(['links', 'endpoints', 'performance_graph'], resource);
  const timelineEndpoint = path<string>(
    ['links', 'endpoints', 'timeline'],
    resource,
  );

  const retrieveTimeline = (): void => {
    if (or(isNil(timelineEndpoint), not(displayEventAnnotations))) {
      setTimeline([]);

      return;
    }

    const [start, end] = getIntervalDates(selectedTimePeriod);

    sendGetTimelineRequest({
      endpoint: timelineEndpoint,
      parameters: {
        limit:
          selectedTimePeriod?.timelineEventsLimit ||
          customTimePeriod.timelineLimit,
        search: {
          conditions: [
            {
              field: 'date',
              values: {
                $gt: start,
                $lt: end,
              },
            },
          ],
        },
      },
    }).then(({ result }) => {
      setTimeline(result);
    });
  };

  useEffect(() => {
    if (isNil(endpoint)) {
      return;
    }

    retrieveTimeline();
  }, [endpoint, selectedTimePeriod, customTimePeriod, displayEventAnnotations]);

  useEffect(() => {
    setElement(graphContainerRef.current);
  }, []);

  const graphEndpoint = useMemo((): string | undefined => {
    if (isNil(endpoint)) {
      return undefined;
    }

    const graphQuerParameters = getGraphQueryParameters({
      endDate: customTimePeriod.end,
      startDate: customTimePeriod.start,
      timePeriod: selectedTimePeriod,
    });

    return `${endpoint}${graphQuerParameters}`;
  }, [
    customTimePeriod.start.toISOString(),
    customTimePeriod.end.toISOString(),
    details,
  ]);

  const addCommentToTimeline = ({ date, comment }): void => {
    const [id] = crypto.getRandomValues(new Uint16Array(1));

    setTimeline([
      ...(timeline as Array<TimelineEvent>),
      {
        contact: { name: alias },
        content: comment,
        date,
        id,
        type: 'comment',
      },
    ]);
  };

  const getIsModalOpened = (value: boolean): void => {
    setIsOpenModalAD(value);
  };

  const getPerformanceGraphRef = (ref): void => {
    setPerformanceGraphRef(ref);
  };

  const sendReloadGraphPerformance = (value: boolean): void => {
    if (!onReload) {
      return;
    }

    onReload(value);
  };

  return (
    <Paper className={classes.graphContainer}>
      <div
        className={classes.graph}
        ref={graphContainerRef as MutableRefObject<HTMLDivElement>}
      >
        <PerformanceGraph
          toggableLegend
          adjustTimePeriod={adjustTimePeriod}
          customTimePeriod={customTimePeriod}
          displayEventAnnotations={displayEventAnnotations}
          endpoint={graphEndpoint}
          getPerformanceGraphRef={getPerformanceGraphRef}
          graphActions={
            !isEditAnomalyDetectionDataDialogOpen && (
              <MemoizedGraphActions
                customTimePeriod={customTimePeriod}
                getIsModalOpened={getIsModalOpened}
                performanceGraphRef={
                  performanceGraphRef as unknown as MutableRefObject<HTMLDivElement | null>
                }
                resourceName={resource?.name as string}
                resourceParentName={resource?.parent?.name}
                resourceType={resource?.type}
                timeline={timeline}
              />
            )
          }
          graphHeight={graphHeight}
          isEditAnomalyDetectionDataDialogOpen={
            isEditAnomalyDetectionDataDialogOpen
          }
          isInViewport={isInViewport}
          limitLegendRows={limitLegendRows}
          modal={
            isOpenModalAD && (
              <EditAnomalyDetectionDataDialog
                isOpen={isOpenModalAD}
                setIsOpen={setIsOpenModalAD}
              >
                {({
                  factorsData,
                  getFactors,
                  openModalConfirmation,
                  isEnvelopeResizingCanceled,
                  isResizeEnvelope,
                  setIsResizeEnvelope,
                }): JSX.Element => (
                  <>
                    {factorsData && (
                      <ExportablePerformanceGraphWithTimeline
                        isEditAnomalyDetectionDataDialogOpen
                        graphHeight={180}
                        resizeEnvelopeData={factorsData}
                        resource={resource}
                      />
                    )}
                    {getFactors && details && details?.sensitivity && (
                      <EditAnomalyDetectionDataDialog.Slider
                        details={details}
                        isEnvelopeResizingCanceled={isEnvelopeResizingCanceled}
                        isResizeEnvelope={isResizeEnvelope}
                        openModalConfirmation={openModalConfirmation}
                        sendFactors={getFactors}
                        sendReloadGraphPerformance={sendReloadGraphPerformance}
                        sensitivity={details.sensitivity}
                        setIsResizeEnvelope={setIsResizeEnvelope}
                      />
                    )}
                  </>
                )}
              </EditAnomalyDetectionDataDialog>
            )
          }
          resizeEnvelopeData={resizeEnvelopeData}
          resource={resource as Resource}
          resourceDetailsUpdated={resourceDetailsUpdated}
          timeline={timeline}
          xAxisTickFormat={
            selectedTimePeriod?.dateTimeFormat ||
            customTimePeriod.xAxisTickFormat
          }
          onAddComment={addCommentToTimeline}
        />
      </div>
    </Paper>
  );
};

export default ExportablePerformanceGraphWithTimeline;
