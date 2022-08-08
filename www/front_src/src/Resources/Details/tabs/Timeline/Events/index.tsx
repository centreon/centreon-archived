import { RefObject } from 'react';

import { equals, last, not, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';
import { Dayjs } from 'dayjs';
import { useAtomValue } from 'jotai/utils';

import { Typography, Paper } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import {
  Timeline,
  TimelineConnector,
  TimelineContent,
  TimelineDot,
  TimelineItem,
  TimelineSeparator,
} from '@mui/lab';

import { useLocaleDateTimeFormat } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

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
  timelineDot: {
    alignItems: 'center',
    display: 'grid',
    height: theme.spacing(3),
    justifyItems: 'center',
    width: theme.spacing(3),
  },
}));

interface Props {
  infiniteScrollTriggerRef: RefObject<HTMLDivElement>;
  timeline: Array<TimelineEvent>;
}

const Events = ({ timeline, infiniteScrollTriggerRef }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { locale } = useAtomValue(userAtom);
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
          const eventsByDate = getEventsByDate({
            events: timeline,
            locale: formattedLocale,
          });

          if (isEmpty(eventsByDate)) {
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

          const areStartAndEndDateEqual =
            not(isEmpty(formattedDivisionDates)) &&
            equals(formattedDivisionDates[1], formattedDivisionDates[3]);

          return (
            <div key={label}>
              <div className={classes.events}>
                <Typography display="inline" variant="h6">
                  {t(label)}
                  <span className={classes.divisionSubtitle}>
                    <Typography display="inline">
                      {areStartAndEndDateEqual
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
                  {eventsByDate.map((event) => {
                    const { id, type } = event;

                    const Event = TimelineEventByType[type];

                    const icon = TimelineIconByType[type];

                    const isNotLastEvent = not(
                      equals(event, last(eventsByDate)),
                    );

                    return (
                      <TimelineItem
                        className={classes.event}
                        key={`${id}-${type}`}
                      >
                        <TimelineSeparator>
                          <TimelineDot
                            className={classes.timelineDot}
                            variant="outlined"
                          >
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
