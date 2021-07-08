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
  const { format } = useLocaleDateTimeFormat();

  const lastEvent = last(timeline.sort(sortEventsByDate));

  return (
    <div>
      {eventsByDateDivisions.map(
        ({
          label,
          getEventsByDate,
          startDate,
          endDate,
        }): JSX.Element | null => {
          const eventsOfTheDivision = getEventsByDate({
            events: timeline,
            locale: locale.substring(0, 2),
          });

          if (isEmpty(eventsOfTheDivision)) {
            return null;
          }

          const formattedStartDate = startDate && (
            <Typography display="inline">
              {[
                t(labelFrom),
                format({
                  date: startDate(locale).toISOString(),
                  formatString: 'LL',
                }),
              ].join(' ')}
            </Typography>
          );

          const formattedEndDate = endDate && (
            <Typography display="inline">
              {[
                t(labelTo).toLowerCase(),
                format({
                  date: endDate(locale).toISOString(),
                  formatString: 'LL',
                }),
              ].join(' ')}
            </Typography>
          );

          return (
            <div key={label}>
              <div className={classes.events}>
                <Typography display="inline" variant="h6">
                  {t(label)}
                  <span className={classes.divisionSubtitle}>
                    {formattedStartDate} {formattedEndDate}
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
