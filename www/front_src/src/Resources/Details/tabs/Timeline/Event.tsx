/* eslint-disable hooks/sort */
// Issue : https://github.com/hiukky/eslint-plugin-hooks/issues/3

import * as React from 'react';

import { prop, isNil, filter, equals } from 'ramda';
import { TFunction, useTranslation } from 'react-i18next';
import dayjs, { Dayjs } from 'dayjs';
import { Chip, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import EventIcon from '@mui/icons-material/Event';
import CommentIcon from '@mui/icons-material/Comment';
import NotificationIcon from '@mui/icons-material/Notifications';
import FaceIcon from '@mui/icons-material/Face';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import {
  labelEvent,
  labelComment,
  labelAcknowledgement,
  labelDowntime,
  labelFrom,
  labelTo,
  labelTries,
  labelNotification,
  labelToday,
  labelYesterday,
  labelThisWeek,
  labelLastWeek,
  labelLastMonth,
  labelLastYear,
  labelBeforeLastYear,
} from '../../../translatedLabels';
import DowntimeChip from '../../../Chip/Downtime';
import AcknowledgeChip from '../../../Chip/Acknowledge';
import CompactStatusChip, {
  useCompactStatusChipStyles,
} from '../CompactStatusChip';
import OutputInformation from '../OutputInformation';

import { TimelineEvent, Type } from './models';

const types: Array<Type> = [
  {
    id: 'event',
    name: labelEvent,
  },

  {
    id: 'notification',
    name: labelNotification,
  },

  {
    id: 'comment',
    name: labelComment,
  },

  {
    id: 'acknowledgement',
    name: labelAcknowledgement,
  },

  {
    id: 'downtime',
    name: labelDowntime,
  },
];

const getTypeIds = (): Array<string> => {
  return types.map(prop('id'));
};

const useStyles = makeStyles((theme) => ({
  event: {
    display: 'flex',
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: theme.spacing(1),
  },
  info: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
  },
  infoHeader: {
    alignItems: 'start',
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
    gridTemplateColumns: 'minmax(80px, auto) auto 1fr',
    marginBottom: theme.spacing(1),
    marginRight: theme.spacing(2),
  },
  title: {
    alignItems: 'center',
    display: 'grid',
    gridAutoColumns: 'auto',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
    justifyContent: 'flex-start',
  },
}));

interface Props {
  event: TimelineEvent;
}

const Date = ({ event }: Props): JSX.Element => {
  const { format } = useLocaleDateTimeFormat();

  const parsedDate = format({ date: event.date, formatString: 'LLLL' });

  return <Typography variant="caption">{parsedDate}</Typography>;
};

const Author = ({ event }: Props): JSX.Element => {
  const classes = useCompactStatusChipStyles();

  const contactName = event.contact?.name || '';

  return (
    <Chip
      className={classes.root}
      icon={<FaceIcon />}
      label={contactName}
      size="small"
      variant="outlined"
    />
  );
};

const EventTimelineEvent = ({ event }: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.infoHeader}>
        <Date event={event} />
        <CompactStatusChip
          label={t(event.status?.name as string)}
          severityCode={event.status?.severity_code as number}
        />
        <Typography
          style={{
            justifySelf: 'end',
          }}
          variant="caption"
        >
          {`${t(labelTries)}: ${event.tries}`}
        </Typography>
      </div>
      <OutputInformation bold content={event.content} />
    </div>
  );
};

const CommentTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.infoHeader}>
        <Date event={event} />
        <div className={classes.title}>
          <Author event={event} />
        </div>
      </div>
      <OutputInformation bold content={event.content} />
    </div>
  );
};

const AcknowledgeTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.infoHeader}>
        <Date event={event} />
        <div className={classes.title}>
          <Author event={event} />
        </div>
      </div>
      <OutputInformation bold content={event.content} />
    </div>
  );
};

const DowntimeTimelineEvent = ({ event }: Props): JSX.Element => {
  const { t } = useTranslation();
  const { format } = useLocaleDateTimeFormat();
  const classes = useStyles();

  const getCaption = (): string => {
    const formattedStartDate = format({
      date: event.startDate as string,
      formatString: 'LLLL',
    });

    const from = `${t(labelFrom)} ${formattedStartDate}`;

    if (isNil(event.endDate)) {
      return from;
    }

    const formattedEndDate = format({
      date: event.endDate as string,
      formatString: 'LLLL',
    });

    return `${from} ${t(labelTo)} ${formattedEndDate}`;
  };

  return (
    <div className={classes.event}>
      <div className={classes.infoHeader}>
        <Typography variant="caption">{getCaption()}</Typography>
        <div className={classes.title}>
          <Author event={event} />
        </div>
      </div>
      <OutputInformation bold content={event.content} />
    </div>
  );
};

const NotificationTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.infoHeader}>
        <Date event={event} />
        <div className={classes.title}>
          <Author event={event} />
        </div>
      </div>
      <OutputInformation bold content={event.content} />
    </div>
  );
};

const TimelineEventByType = {
  acknowledgement: AcknowledgeTimelineEvent,
  comment: CommentTimelineEvent,
  downtime: DowntimeTimelineEvent,
  event: EventTimelineEvent,
  notification: NotificationTimelineEvent,
};

const TimelineIconByType = {
  acknowledgement: (t: TFunction): JSX.Element => (
    <AcknowledgeChip aria-label={t(labelAcknowledgement)} />
  ),
  comment: (t: TFunction): JSX.Element => (
    <CommentIcon aria-label={t(labelComment)} color="primary" />
  ),
  downtime: (t: TFunction): JSX.Element => (
    <DowntimeChip aria-label={t(labelDowntime)} />
  ),
  event: (t: TFunction): JSX.Element => (
    <EventIcon aria-label={t(labelEvent)} color="primary" />
  ),
  notification: (t: TFunction): JSX.Element => (
    <NotificationIcon aria-label={t(labelNotification)} color="primary" />
  ),
};

const sortEventsByDate = (
  { date: prevDate }: TimelineEvent,
  { date: nextDate }: TimelineEvent,
): number => dayjs(nextDate).valueOf() - dayjs(prevDate).valueOf();

const getWeeksInDays = (weeks: number): number => weeks * -7;

interface GetDateFromNowWithWeeksProps {
  locale: string;
  weeks: number;
}

const getDateFromNowWithWeeks = ({
  weeks,
  locale,
}: GetDateFromNowWithWeeksProps): Dayjs =>
  dayjs().locale(locale).weekday(getWeeksInDays(weeks));

const thisWeek = 0;
const lastWeek = 1;
const lastMonth = lastWeek + 4;
const lastYear = lastMonth + 52;

interface GetEventsByDateInWeeksProps {
  events: Array<TimelineEvent>;
  fromNumberOfWeeks: number;
  inclusionPolicy?: '[)' | '()' | '[]' | '(]' | undefined;
  locale: string;
  toNumberOfWeeks: number;
}

const getEventsByDateInWeeks = ({
  events,
  locale,
  fromNumberOfWeeks,
  toNumberOfWeeks,
  inclusionPolicy = '[)',
}: GetEventsByDateInWeeksProps): Array<TimelineEvent> =>
  events
    .filter(
      ({ date }) =>
        dayjs(date).isBetween(
          getDateFromNowWithWeeks({ locale, weeks: fromNumberOfWeeks }),
          getDateFromNowWithWeeks({ locale, weeks: toNumberOfWeeks }),
          'day',
          inclusionPolicy,
        ),
      events,
    )
    .sort(sortEventsByDate);

interface GetEventsByDateProps {
  events: Array<TimelineEvent>;
  locale: string;
}

interface EventsByDateDivisions {
  endDate?: (locale: string) => Dayjs;
  getEventsByDate: (props: GetEventsByDateProps) => Array<TimelineEvent>;
  label: string;
  startDate?: (locale: string) => Dayjs;
}

const eventsByDateDivisions: Array<EventsByDateDivisions> = [
  {
    getEventsByDate: ({ events }: GetEventsByDateProps): Array<TimelineEvent> =>
      events
        .filter(({ date }) => dayjs(date).isToday(), events)
        .sort(sortEventsByDate),
    label: labelToday,
  },
  {
    getEventsByDate: ({ events }): Array<TimelineEvent> =>
      events
        .filter(({ date }) => dayjs(date).isYesterday(), events)
        .sort(sortEventsByDate),
    label: labelYesterday,
  },
  {
    endDate: (): Dayjs => dayjs().subtract(2, 'day'),
    getEventsByDate: ({ events, locale }): Array<TimelineEvent> =>
      (
        filter(({ date }) => {
          const startWeekday = dayjs()
            .locale(locale as string)
            .subtract(2, 'day')
            .weekday();

          if (equals(startWeekday, 6)) {
            return false;
          }

          return equals(startWeekday, 0)
            ? dayjs(date).isSame(dayjs().subtract(2, 'day'), 'day')
            : dayjs(date).isBetween(
                dayjs()
                  .locale(locale as string)
                  .weekday(getWeeksInDays(thisWeek)),
                dayjs().subtract(2, 'day'),
                'day',
                '[]',
              );
        }, events) as Array<TimelineEvent>
      ).sort(sortEventsByDate),
    label: labelThisWeek,
    startDate: (locale): Dayjs =>
      getDateFromNowWithWeeks({ locale, weeks: thisWeek }),
  },
  {
    endDate: (locale: string): Dayjs =>
      getDateFromNowWithWeeks({ locale, weeks: thisWeek }).subtract(1, 'day'),
    getEventsByDate: (props): Array<TimelineEvent> =>
      getEventsByDateInWeeks({
        ...props,
        fromNumberOfWeeks: lastWeek,
        inclusionPolicy: '[)',
        toNumberOfWeeks: thisWeek,
      }),
    label: labelLastWeek,
    startDate: (locale: string): Dayjs =>
      getDateFromNowWithWeeks({ locale, weeks: lastWeek }),
  },
  {
    endDate: (locale: string): Dayjs =>
      getDateFromNowWithWeeks({ locale, weeks: lastWeek }).subtract(1, 'day'),
    getEventsByDate: (props): Array<TimelineEvent> =>
      getEventsByDateInWeeks({
        ...props,
        fromNumberOfWeeks: lastMonth,
        toNumberOfWeeks: lastWeek,
      }),
    label: labelLastMonth,
    startDate: (locale: string): Dayjs =>
      getDateFromNowWithWeeks({ locale, weeks: lastMonth }),
  },
  {
    endDate: (locale: string): Dayjs =>
      getDateFromNowWithWeeks({ locale, weeks: lastMonth }).subtract(1, 'day'),
    getEventsByDate: (props): Array<TimelineEvent> =>
      getEventsByDateInWeeks({
        ...props,
        fromNumberOfWeeks: lastYear,
        toNumberOfWeeks: lastMonth,
      }),
    label: labelLastYear,
    startDate: (locale: string): Dayjs =>
      getDateFromNowWithWeeks({ locale, weeks: lastYear }),
  },
  {
    getEventsByDate: ({ events, locale }): Array<TimelineEvent> =>
      (
        filter(
          ({ date }) =>
            dayjs(date).isBefore(
              getDateFromNowWithWeeks({ locale, weeks: lastYear }),
              'day',
            ),
          events,
        ) as Array<TimelineEvent>
      ).sort(sortEventsByDate),
    label: labelBeforeLastYear,
    startDate: (locale: string): Dayjs =>
      getDateFromNowWithWeeks({ locale, weeks: lastYear }).subtract(1, 'day'),
  },
];

export {
  TimelineEventByType,
  types,
  getTypeIds,
  TimelineIconByType,
  eventsByDateDivisions,
  sortEventsByDate,
};
