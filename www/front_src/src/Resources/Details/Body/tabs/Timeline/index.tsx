import * as React from 'react';

import {
  useRequest,
  getData,
  ListingModel,
  ContentWithCircularLoading,
} from '@centreon/ui';

import { makeStyles, Paper, Typography } from '@material-ui/core';
import {
  isNil,
  groupBy,
  groupWith,
  equals,
  toPairs,
  reduceBy,
  merge,
  pipe,
  prop,
  sortBy,
  sort,
  gt,
} from 'ramda';
import { Skeleton } from '@material-ui/lab';
import { getFormattedDate } from '../../../../dateTime';
import { ResourceEndpoints } from '../../../../models';
import EventChip from './Chip/Event';
import { TimelineEventByType } from './Event';
import { TimelineEvent } from './models';

interface Props {
  endpoints: Pick<ResourceEndpoints, 'timeline'>;
}

type TimelineListing = ListingModel<TimelineEventModel>;

const useStyles = makeStyles((theme) => ({
  container: {
    width: '100%',
    height: '100%',
  },
  events: {
    display: 'grid',
    gridAutoFlow: 'row',
    padding: theme.spacing(1),
    gridGap: theme.spacing(1),
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

  const [timeline, setTimeline] = React.useState<TimelineListing>();

  const { timeline: timelineEndpoint } = endpoints;

  const { sendRequest, sending } = useRequest<TimelineListing>({
    request: getData,
  });

  React.useEffect(() => {
    sendRequest('http://localhost:5000/mock/timeline').then(setTimeline);
  }, []);

  const timelineResult = timeline?.result || [];

  const eventsByDate = pipe(
    reduceBy<TimelineEvent, Array<TimelineEvent>>(
      (acc, event) => acc.concat(event),
      [] as Array<TimelineEvent>,
      pipe(prop('date'), getFormattedDate),
    ),
    toPairs,
    sortBy(([date]) => date),
  )(timelineResult);

  return (
    <div className={classes.container}>
      <div className={classes.events}>
        {isNil(timeline) ? (
          <LoadingSkeleton />
        ) : (
          eventsByDate.map(
            ([date, events]): JSX.Element => {
              return (
                <div key={date} className={classes.events}>
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
              );
            },
          )
        )}
      </div>
    </div>
  );
};

export default TimelineTab;
