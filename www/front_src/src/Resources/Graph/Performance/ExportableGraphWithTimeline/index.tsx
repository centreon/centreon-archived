import * as React from 'react';

import { path, isNil, or, not } from 'ramda';

import { Paper, Theme, makeStyles } from '@material-ui/core';

import { useRequest, ListingModel } from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import { TimelineEvent } from '../../../Details/tabs/Timeline/models';
import { listTimelineEvents } from '../../../Details/tabs/Timeline/api';
import { listTimelineEventsDecoder } from '../../../Details/tabs/Timeline/api/decoders';
import PerformanceGraph from '..';
import {
  CustomTimePeriod,
  TimePeriod,
} from '../../../Details/tabs/Graph/models';
import { Resource } from '../../../models';
import { ResourceDetails } from '../../../Details/models';
import { AdjustTimePeriodProps, GraphOptionId } from '../models';
import { useIntersection } from '../useGraphIntersection';

import { defaultGraphOptions, useGraphOptionsContext } from './useGraphOptions';

const useStyles = makeStyles((theme: Theme) => ({
  graph: {
    height: '100%',
    margin: 'auto',
    width: '100%',
  },
  graphContainer: {
    display: 'grid',
    gridTemplateRows: '1fr',
    padding: theme.spacing(2, 1, 1),
  },
}));

interface Props {
  adjustTimePeriod?: (props: AdjustTimePeriodProps) => void;
  customTimePeriod: CustomTimePeriod;
  getIntervalDates: () => [string, string];
  graphHeight: number;
  limitLegendRows?: boolean;
  periodQueryParameters: string;
  resource?: Resource | ResourceDetails;
  resourceDetailsUpdated: boolean;
  selectedTimePeriod: TimePeriod | null;
}

const ExportablePerformanceGraphWithTimeline = ({
  resource,
  selectedTimePeriod,
  getIntervalDates,
  periodQueryParameters,
  graphHeight,
  customTimePeriod,
  adjustTimePeriod,
  resourceDetailsUpdated,
  limitLegendRows,
}: Props): JSX.Element => {
  const classes = useStyles();

  const { alias } = useUserContext();

  const { sendRequest: sendGetTimelineRequest } = useRequest<
    ListingModel<TimelineEvent>
  >({
    decoder: listTimelineEventsDecoder,
    request: listTimelineEvents,
  });

  const [timeline, setTimeline] = React.useState<Array<TimelineEvent>>();
  const graphOptions =
    useGraphOptionsContext()?.graphOptions || defaultGraphOptions;
  const graphContainerRef = React.useRef<HTMLElement | null>(null);

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

    const [start, end] = getIntervalDates();

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

  React.useEffect(() => {
    if (isNil(endpoint)) {
      return;
    }

    retrieveTimeline();
  }, [endpoint, selectedTimePeriod, customTimePeriod, displayEventAnnotations]);

  React.useEffect(() => {
    setElement(graphContainerRef.current);
  }, []);

  const getEndpoint = (): string | undefined => {
    if (isNil(endpoint)) {
      return undefined;
    }

    return `${endpoint}${periodQueryParameters}`;
  };

  const addCommentToTimeline = ({ date, comment }): void => {
    setTimeline([
      ...(timeline as Array<TimelineEvent>),
      {
        contact: { name: alias },
        content: comment,
        date,
        id: Math.random(),
        type: 'comment',
      },
    ]);
  };

  return (
    <Paper className={classes.graphContainer}>
      <div
        className={classes.graph}
        ref={graphContainerRef as React.MutableRefObject<HTMLDivElement>}
      >
        <PerformanceGraph
          toggableLegend
          adjustTimePeriod={adjustTimePeriod}
          customTimePeriod={customTimePeriod}
          displayEventAnnotations={displayEventAnnotations}
          endpoint={getEndpoint()}
          graphHeight={graphHeight}
          isInViewport={isInViewport}
          limitLegendRows={limitLegendRows}
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
