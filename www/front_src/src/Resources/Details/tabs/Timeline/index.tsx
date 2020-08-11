import * as React from 'react';

import {
  useRequest,
  ListingModel,
  useIntersectionObserver,
} from '@centreon/ui';

import {
  makeStyles,
  Paper,
  Typography,
  CircularProgress,
} from '@material-ui/core';
import {
  equals,
  toPairs,
  reduceBy,
  pipe,
  prop,
  last,
  isEmpty,
  head,
  sortWith,
  descend,
} from 'ramda';
import { Skeleton } from '@material-ui/lab';

import { getFormattedDate } from '../../../../dateTime';
import { ResourceEndpoints } from '../../../../models';
import { TimelineEventByType } from './Event';
import { TimelineEvent } from './models';
import { listTimelineEventsDecoder } from './api/decoders';
import { listTimelineEvents } from './api';

interface Props {
  endpoints: Pick<ResourceEndpoints, 'timeline'>;
}

type TimelineListing = ListingModel<TimelineEvent>;

const useStyles = makeStyles((theme) => ({
  container: {
    width: '100%',
    height: '100%',
    display: 'grid',
    alignItems: 'center',
    justifyItems: 'center',
    alignContent: 'flex-start',
    gridGap: theme.spacing(1),
  },
  events: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
    width: '100%',
  },
  event: {
    display: 'grid',
    gridAutoFlow: 'columns',
    gridTemplateColumns: 'auto 1fr auto',
    padding: theme.spacing(1),
    gridGap: theme.spacing(2),
    alignItems: 'center',
  },
}));

const LoadingSkeleton = (): JSX.Element => {
  return (
    <>
      <Skeleton width={125} height={20} style={{ transform: 'none' }} />
      <Skeleton height={100} style={{ transform: 'none' }} />
      <Skeleton height={100} style={{ transform: 'none' }} />
    </>
  );
};

const TimelineTab = ({ endpoints }: Props): JSX.Element => {
  const classes = useStyles();

  const [timeline, setTimeline] = React.useState<Array<TimelineEvent>>([]);
  const [page, setPage] = React.useState(1);
  const [limit] = React.useState(10);
  const [maxPage, setMaxPage] = React.useState(1);

  const { timeline: timelineEndpoint } = endpoints;

  const { sendRequest, sending } = useRequest<TimelineListing>({
    request: listTimelineEvents,
    decoder: listTimelineEventsDecoder,
  });

  React.useEffect(() => {
    sendRequest({
      endpoint: timelineEndpoint,
      params: { page, limit },
    }).then(({ result, meta }) => {
      setTimeline(timeline.concat(result));

      setMaxPage(Math.ceil(meta.total / meta.limit));
    });
  }, [page]);

  const infiniteScrollTriggerRef = useIntersectionObserver({
    maxPage,
    page,
    loading: sending,
    action: () => {
      setPage(page + 1);
    },
  });

  type DateEvents = Array<[string, Array<TimelineEvent>]>;

  const eventsByDate = pipe(
    reduceBy<TimelineEvent, Array<TimelineEvent>>(
      (acc, event) => acc.concat(event),
      [],
      pipe(prop('date'), getFormattedDate),
    ),
    toPairs,
    sortWith([descend(head)]),
  )(timeline) as DateEvents;

  const dates = eventsByDate.map(head);

  const loading = isEmpty(timeline) && sending;
  const loadingMore = !isEmpty(timeline) && sending;

  return (
    <div className={classes.container}>
      <div className={classes.events}>
        {loading ? (
          <LoadingSkeleton />
        ) : (
          eventsByDate.map(
            ([date, events]): JSX.Element => {
              const isLastDate = equals(last(dates), date);

              return (
                <React.Fragment key={date}>
                  <div className={classes.events}>
                    <Typography variant="h6">{date}</Typography>

                    {events.map((event) => {
                      const { id, type } = event;

                      const Event = TimelineEventByType[type];

                      return (
                        <Paper key={id} className={classes.event}>
                          <Event event={event} />
                        </Paper>
                      );
                    })}
                  </div>
                  {isLastDate && <div ref={infiniteScrollTriggerRef} />}
                </React.Fragment>
              );
            },
          )
        )}
      </div>
      {loadingMore && <CircularProgress />}
    </div>
  );
};

export default TimelineTab;
