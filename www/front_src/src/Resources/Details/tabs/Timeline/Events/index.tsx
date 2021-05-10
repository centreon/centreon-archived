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

import { useLocaleDateTimeFormat } from '@centreon/ui';

import { TimelineEvent } from '../models';
import { TimelineEventByType } from '../Event';

const useStyles = makeStyles((theme) => ({
  events: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
    width: '100%',
  },
}));

type DateEvents = Array<[string, Array<TimelineEvent>]>;

interface Props {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  timeline: Array<TimelineEvent>;
}

const Events = ({ timeline, infiniteScrollTriggerRef }: Props): JSX.Element => {
  const classes = useStyles();
  const { toDate } = useLocaleDateTimeFormat();

  const eventsByDate = pipe(
    reduceBy<TimelineEvent, Array<TimelineEvent>>(
      (acc, event) => acc.concat(event),
      [],
      pipe(prop('date'), toDate),
    ),
    toPairs,
    sortWith([descend(pipe(head, Date.parse))]),
  )(timeline) as DateEvents;

  const dates = eventsByDate.map(head);

  return (
    <div>
      {eventsByDate.map(([date, events]): JSX.Element => {
        const isLastDate = equals(last(dates), date);

        return (
          <div key={date}>
            <div className={classes.events}>
              <Typography variant="h6">{date}</Typography>

              {events.map((event) => {
                const { id, type } = event;

                const Event = TimelineEventByType[type];

                return (
                  <Paper key={`${id}-${type}`}>
                    <Event event={event} />
                  </Paper>
                );
              })}
            </div>
            {isLastDate && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </div>
  );
};

export default Events;
