import {
  MutableRefObject,
  useEffect,
  useMemo,
  useRef,
  useState,
  ReactNode,
} from 'react';

import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { isNil, not, or, path } from 'ramda';

import { Paper, Theme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { ListingModel, useRequest } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import PerformanceGraph from '..';
import { detailsAtom } from '../../../Details/detailsAtoms';
import { ResourceDetails } from '../../../Details/models';
import { listTimelineEvents } from '../../../Details/tabs/Timeline/api';
import { listTimelineEventsDecoder } from '../../../Details/tabs/Timeline/api/decoders';
import { TimelineEvent } from '../../../Details/tabs/Timeline/models';
import { Resource } from '../../../models';
import { CustomFactorsData } from '../AnomalyDetection/models';
import MemoizedGraphActions from '../GraphActions';
import { GraphOptionId } from '../models';
import {
  adjustTimePeriodDerivedAtom,
  customTimePeriodAtom,
  getDatesDerivedAtom,
  graphQueryParametersDerivedAtom,
  resourceDetailsUpdatedAtom,
  selectedTimePeriodAtom,
} from '../TimePeriods/timePeriodAtoms';
import { useIntersection } from '../useGraphIntersection';

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
  additionalData?: CustomFactorsData | null;
  graphHeight: number;
  interactWithGraph: boolean;
  isRenderAdditionalGraphActions: boolean;
  limitLegendRows?: boolean;
  renderAdditionalGraphAction?: ReactNode;
  renderAdditionalLines?: (args) => ReactNode;
  resource?: Resource | ResourceDetails;
}

const ExportablePerformanceGraphWithTimeline = ({
  resource,
  graphHeight,
  limitLegendRows,
  interactWithGraph,
  additionalData,
  renderAdditionalGraphAction,
  isRenderAdditionalGraphActions,
  renderAdditionalLines,
}: Props): JSX.Element => {
  const classes = useStyles();
  const [timeline, setTimeline] = useState<Array<TimelineEvent>>();
  const [performanceGraphRef, setPerformanceGraphRef] =
    useState<HTMLDivElement | null>(null);

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

  const getPerformanceGraphRef = (ref): void => {
    setPerformanceGraphRef(ref);
  };

  return (
    <Paper className={classes.graphContainer}>
      <div
        className={classes.graph}
        ref={graphContainerRef as MutableRefObject<HTMLDivElement>}
      >
        <PerformanceGraph
          toggableLegend
          additionalData={additionalData}
          adjustTimePeriod={adjustTimePeriod}
          customTimePeriod={customTimePeriod}
          displayEventAnnotations={displayEventAnnotations}
          endpoint={graphEndpoint}
          getPerformanceGraphRef={getPerformanceGraphRef}
          graphActions={
            <MemoizedGraphActions
              customTimePeriod={customTimePeriod}
              isRenderAdditionalGraphActions={isRenderAdditionalGraphActions}
              open={interactWithGraph}
              performanceGraphRef={
                performanceGraphRef as unknown as MutableRefObject<HTMLDivElement | null>
              }
              renderAdditionalGraphActions={renderAdditionalGraphAction}
              resourceName={resource?.name as string}
              resourceParentName={resource?.parent?.name}
              timeline={timeline}
            />
          }
          graphHeight={graphHeight}
          interactWithGraph={interactWithGraph}
          isInViewport={isInViewport}
          limitLegendRows={limitLegendRows}
          renderAdditionalLines={renderAdditionalLines}
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
