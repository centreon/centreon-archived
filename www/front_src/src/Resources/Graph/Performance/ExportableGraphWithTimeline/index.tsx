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

import { defaultGraphOptions, useGraphOptionsContext } from './useGraphOptions';

const useStyles = makeStyles((theme: Theme) => ({
  graphContainer: {
    display: 'grid',
    padding: theme.spacing(2, 1, 1),
    gridTemplateRows: '1fr',
  },
  graph: {
    margin: 'auto',
    height: '100%',
    width: '100%',
  },
}));

interface Props {
  resource?: Resource | ResourceDetails;
  selectedTimePeriod: TimePeriod | null;
  getIntervalDates: () => [string, string];
  periodQueryParameters: string;
  graphHeight: number;
  onTooltipDisplay?: (position?: [number, number]) => void;
  tooltipPosition?: [number, number];
  customTimePeriod: CustomTimePeriod;
  resourceDetailsUpdated: boolean;
  adjustTimePeriod?: (props: AdjustTimePeriodProps) => void;
}

const ExportablePerformanceGraphWithTimeline = ({
  resource,
  selectedTimePeriod,
  getIntervalDates,
  periodQueryParameters,
  graphHeight,
  onTooltipDisplay,
  tooltipPosition,
  customTimePeriod,
  adjustTimePeriod,
  resourceDetailsUpdated,
}: Props): JSX.Element => {
  const classes = useStyles();

  const { alias } = useUserContext();

  const { sendRequest: sendGetTimelineRequest } = useRequest<
    ListingModel<TimelineEvent>
  >({
    request: listTimelineEvents,
    decoder: listTimelineEventsDecoder,
  });

  const [timeline, setTimeline] = React.useState<Array<TimelineEvent>>();
  const graphOptions =
    useGraphOptionsContext()?.graphOptions || defaultGraphOptions;

  const displayTooltipValues = path<boolean>(
    [GraphOptionId.displayTooltips, 'value'],
    graphOptions,
  );
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
        id: Math.random(),
        type: 'comment',
        date,
        content: comment,
        contact: { name: alias },
      },
    ]);
  };

  return (
    <Paper className={classes.graphContainer}>
      <div className={classes.graph}>
        <PerformanceGraph
          endpoint={getEndpoint()}
          graphHeight={graphHeight}
          xAxisTickFormat={
            selectedTimePeriod?.dateTimeFormat ||
            customTimePeriod.xAxisTickFormat
          }
          toggableLegend
          resource={resource as Resource}
          timeline={timeline}
          onAddComment={addCommentToTimeline}
          onTooltipDisplay={onTooltipDisplay}
          tooltipPosition={tooltipPosition}
          adjustTimePeriod={adjustTimePeriod}
          customTimePeriod={customTimePeriod}
          resourceDetailsUpdated={resourceDetailsUpdated}
          displayTooltipValues={displayTooltipValues}
          displayEventAnnotations={displayEventAnnotations}
        />
      </div>
    </Paper>
  );
};

export default ExportablePerformanceGraphWithTimeline;
