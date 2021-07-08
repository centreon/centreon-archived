import * as React from 'react';

import { equals, last, not, isEmpty } from 'ramda';
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

import { useUserContext } from '@centreon/ui-context';

import { TimelineEvent } from '../models';
import {
  eventsByDateDivision,
  TimelineEventByType,
  TimelineIconByType,
  sortEventsByDate,
} from '../Event';

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

interface Props {
  infiniteScrollTriggerRef: React.RefObject<HTMLDivElement>;
  timeline: Array<TimelineEvent>;
}

const Events = ({ timeline, infiniteScrollTriggerRef }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { locale } = useUserContext();

  const lastEvent = last(timeline.sort(sortEventsByDate));

  return (
    <div>
      {eventsByDateDivision.map(
        ({ label, getEventsByDate, displayFullDate }): JSX.Element | null => {
          const eventsOfTheDivision = getEventsByDate({
            events: timeline,
            locale,
          });

          if (isEmpty(eventsOfTheDivision)) {
            return null;
          }

          return (
            <div key={label}>
              <div className={classes.events}>
                <Typography variant="h6">{label}</Typography>
                <Timeline className={classes.timeline}>
                  {eventsOfTheDivision.map((event) => {
                    const { id, type } = event;

                    const Event = TimelineEventByType[type];

                    const icon = TimelineIconByType[type];

                    const isNotLastEvent = not(
                      equals(event, last(eventsOfTheDivision)),
                    );

                    return (
                      <TimelineItem
                        className={classes.event}
                        key={`${id}-${type}`}
                      >
                        <TimelineSeparator>
                          <TimelineDot variant="outlined">
                            {icon(t)}
                          </TimelineDot>
                          {isNotLastEvent && <TimelineConnector />}
                        </TimelineSeparator>
                        <TimelineContent>
                          <Paper>
                            <Event
                              displayFullDate={displayFullDate}
                              event={event}
                            />
                          </Paper>
                          {equals(lastEvent, event) && (
                            <div ref={infiniteScrollTriggerRef} />
                          )}
                        </TimelineContent>
                      </TimelineItem>
                    );
                  })}
                </Timeline>
              </div>
            </div>
          );
        },
      )}
    </div>
  );
};

export default Events;
