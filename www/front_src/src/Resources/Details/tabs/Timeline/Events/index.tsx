import { RefObject } from 'react';

import { equals, last, not, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';
import { Dayjs } from 'dayjs';
import { useAtomValue } from 'jotai/utils';

import { Typography, Paper, Divider } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import {
  Timeline,
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
  contentContainer: {
    paddingBottom: 0,
    paddingTop: 0,
  },
  divider: {
    backgroundColor: theme.palette.action.disabled,
  },
  dividerContainer: {
    display: 'flex',
    height: 12,
    paddingLeft: 18,
  },
  divisionSubtitle: {
    marginLeft: theme.spacing(4),
  },
  event: {
    '&:before': {
      flex: 0,
      padding: 0,
    },
    alignItems: 'center',
    minHeight: theme.spacing(7),
  },
  events: {
    display: 'grid',
    gridAutoFlow: 'row',
    width: '100%',
  },
  header: {
    paddingBottom: theme.spacing(1),
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

          const eventDate = areStartAndEndDateEqual
            ? format({
                date: (startDate?.(formattedLocale) as Dayjs)?.toISOString(),
                formatString: 'LL',
              })
            : formattedDivisionDates.join(' ');

          return (
            <div key={label}>
              <div className={classes.events}>
                <Timeline className={classes.timeline}>
                  <Typography
                    className={classes.header}
                    display="inline"
                    variant="h6"
                  >
                    {t(label)}
                    <span className={classes.divisionSubtitle}>
                      <Typography display="inline">{eventDate}</Typography>
                    </span>
                  </Typography>
                  {eventsByDate.map((event) => {
                    const { id, type } = event;

                    const Event = TimelineEventByType[type];

                    const icon = TimelineIconByType[type];

                    const isNotLastEvent = not(
                      equals(event, last(eventsByDate)),
                    );

                    return (
                      <>
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
                          </TimelineSeparator>
                          <TimelineContent className={classes.contentContainer}>
                            <Paper>
                              <Event event={event} />
                            </Paper>
                            {equals(lastEvent, event) && (
                              <div ref={infiniteScrollTriggerRef} />
                            )}
                          </TimelineContent>
                        </TimelineItem>
                        {isNotLastEvent && (
                          <div className={classes.dividerContainer}>
                            <Divider
                              flexItem
                              className={classes.divider}
                              orientation="vertical"
                            />
                          </div>
                        )}
                      </>
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
