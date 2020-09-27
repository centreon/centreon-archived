import * as React from 'react';
import {
  reduceBy,
  pipe,
  prop,
  toPairs,
  sortWith,
  descend,
  head,
  equals,
  last,
} from 'ramda';

import { Typography, Paper, makeStyles } from '@material-ui/core';

import { useIntersectionObserver } from '@centreon/ui';

import { getFormattedDate } from '../../../../dateTime';
import { TimelineEvent } from '../models';
import { TimelineEventByType } from '../Event';

const useStyles = makeStyles((theme) => ({
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

type DateEvents = Array<[string, Array<TimelineEvent>]>;

interface Props {
  timeline: Array<TimelineEvent>;
  total: number;
  limit: number;
  page: number;
  loading: boolean;
  onLoadMore: () => void;
}

const Events = ({
  timeline,
  total,
  limit,
  page,
  loading,
  onLoadMore,
}: Props): JSX.Element => {
  const classes = useStyles();

  const maxPage = Math.ceil(total / limit);

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

  const infiniteScrollTriggerRef = useIntersectionObserver({
    maxPage,
    page,
    loading,
    action: onLoadMore,
  });

  return (
    <>
      {eventsByDate.map(
        ([date, events]): JSX.Element => {
          const isLastDate = equals(last(dates), date);

          return (
            <div key={date}>
              <div className={classes.events}>
                <Typography variant="h6">{date}</Typography>

                {events.map((event) => {
                  const { id, type } = event;

                  const Event = TimelineEventByType[type];

                  return (
                    <Paper key={`${id}-${type}`} className={classes.event}>
                      <Event event={event} />
                    </Paper>
                  );
                })}
              </div>
              {isLastDate && <div ref={infiniteScrollTriggerRef} />}
            </div>
          );
        },
      )}
    </>
  );
};

export default Events;
