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
  not,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import { Typography, Paper, makeStyles } from '@material-ui/core';
import {
  Timeline,
  TimelineConnector,
  TimelineContent,
  TimelineDot,
  TimelineItem,
  TimelineSeparator,
} from '@material-ui/lab';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import { TimelineEvent } from '../models';
import { TimelineEventByType, TimelineIconByType, types } from '../Event';

const useStyles = makeStyles((theme) => ({
  event: {
    '&:before': {
      flex: 0,
      padding: 0,
    },
  },
  events: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
    width: '100%',
  },
  timeline: {
    margin: 0,
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
  const { t } = useTranslation();

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
              <Timeline className={classes.timeline}>
                {events.map((event) => {
                  const { id, type } = event;

                  const Event = TimelineEventByType[type];

                  const icon = TimelineIconByType[type];

                  const isNotLastEvent = not(equals(event, last(events)));

                  return (
                    <TimelineItem
                      className={classes.event}
                      key={`${id}-${type}`}
                    >
                      <TimelineSeparator>
                        <TimelineDot variant="outlined">{icon(t)}</TimelineDot>
                        {isNotLastEvent && <TimelineConnector />}
                      </TimelineSeparator>
                      <TimelineContent>
                        <Paper>
                          <Event event={event} />
                        </Paper>
                      </TimelineContent>
                    </TimelineItem>
                  );
                })}
              </Timeline>
            </div>
            {isLastDate && <div ref={infiniteScrollTriggerRef} />}
          </div>
        );
      })}
    </div>
  );
};

export default Events;
