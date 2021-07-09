import * as React from 'react';

import { equals, last, not, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';
import { Dayjs } from 'dayjs';

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
import { useLocaleDateTimeFormat } from '@centreon/centreon-frontend/packages/centreon-ui/src';

import { labelFrom, labelTo } from '../../../../translatedLabels';
import { TimelineEvent } from '../models';
import {
  eventsByDateDivisions,
  TimelineEventByType,
  TimelineIconByType,
  sortEventsByDate,
} from '../Event';

const useStyles = makeStyles((theme) => ({
  divisionSubtitle: {
    marginLeft: theme.spacing(4),
  },
  event: {
    '&:before': {
      flex: 0,
      padding: 0,
    },
    minHeight: theme.spacing(7),
  },
  events: {
    display: 'grid',
    gridAutoFlow: 'row',
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
  const { format } = useLocaleDateTimeFormat();

  const lastEvent = last(timeline.sort(sortEventsByDate));

  const formattedLocale = locale.substring(0, 2);

  return (
    <div aria-label="test">
      {eventsByDateDivisions.map(
        ({
          label,
          getEventsByDate,
          startDate,
          endDate,
        }): JSX.Element | null => {
          const eventsOfTheDivision = getEventsByDate({
            events: timeline,
            locale: formattedLocale,
          });

          if (isEmpty(eventsOfTheDivision)) {
            return null;
          }

          const formattedStartDate = startDate
            ? [
                t(labelFrom),
                format({
                  date: startDate(formattedLocale).toISOString(),
                  formatString: 'LL',
                }),
              ]
            : [];

          const formattedDivisionDates = endDate
            ? [
                ...(formattedStartDate || []),
                t(labelTo).toLowerCase(),
                format({
                  date: endDate(formattedLocale).toISOString(),
                  formatString: 'LL',
                }),
              ]
            : formattedStartDate;

          const areStartAndEndDateEquals =
            not(isEmpty(formattedDivisionDates)) &&
            equals(formattedDivisionDates[1], formattedDivisionDates[3]);

          return (
            <div key={label}>
              <div className={classes.events}>
                <Typography display="inline" variant="h6">
                  {t(label)}
                  <span className={classes.divisionSubtitle}>
                    <Typography display="inline">
                      {areStartAndEndDateEquals
                        ? format({
                            date: (
                              startDate?.(formattedLocale) as Dayjs
                            )?.toISOString(),
                            formatString: 'LL',
                          })
                        : formattedDivisionDates.join(' ')}
                    </Typography>
                  </span>
                </Typography>
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
                            <Event event={event} />
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
