import * as React from 'react';

import { prop, isNil, filter, sortBy, pipe, sort } from 'ramda';
import { TFunction, useTranslation } from 'react-i18next';
import dayjs from 'dayjs';

import { makeStyles, Chip, Typography } from '@material-ui/core';
import EventIcon from '@material-ui/icons/Event';
import CommentIcon from '@material-ui/icons/Comment';
import NotificationIcon from '@material-ui/icons/Notifications';
import FaceIcon from '@material-ui/icons/Face';

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
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'columns',
    gridGap: theme.spacing(2),
    gridTemplateColumns: 'auto 1fr auto',
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
  displayFullDate?: boolean;
  event: TimelineEvent;
}

const Date = ({ event, displayFullDate }: Props): JSX.Element => {
  const { toTime, toDateTime } = useLocaleDateTimeFormat();

  const parseDate = displayFullDate ? toDateTime : toTime;

  return <Typography variant="caption">{parseDate(event.date)}</Typography>;
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

const EventTimelineEvent = ({ event, displayFullDate }: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.info}>
        <div className={classes.infoHeader}>
          <Date displayFullDate={displayFullDate} event={event} />
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
    </div>
  );
};

const CommentTimelineEvent = ({
  event,
  displayFullDate,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.info}>
        <div className={classes.infoHeader}>
          <Date displayFullDate={displayFullDate} event={event} />
          <div className={classes.title}>
            <Author event={event} />
          </div>
        </div>
        <OutputInformation bold content={event.content} />
      </div>
    </div>
  );
};

const AcknowledgeTimelineEvent = ({
  event,
  displayFullDate,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.info}>
        <div className={classes.infoHeader}>
          <Date displayFullDate={displayFullDate} event={event} />
          <div className={classes.title}>
            <Author event={event} />
          </div>
        </div>
        <OutputInformation bold content={event.content} />
      </div>
    </div>
  );
};

const DowntimeTimelineEvent = ({ event }: Props): JSX.Element => {
  const { t } = useTranslation();
  const { toDateTime } = useLocaleDateTimeFormat();
  const classes = useStyles();

  const getCaption = (): string => {
    const formattedStartDate = toDateTime(event.startDate as string);

    const from = `${t(labelFrom)} ${formattedStartDate}`;

    if (isNil(event.endDate)) {
      return from;
    }

    const formattedEndDate = toDateTime(event.endDate);

    return `${from} ${t(labelTo)} ${formattedEndDate}`;
  };

  return (
    <div className={classes.event}>
      <div className={classes.info}>
        <div className={classes.infoHeader}>
          <Typography variant="caption">{getCaption()}</Typography>
          <div className={classes.title}>
            <Author event={event} />
          </div>
        </div>
        <OutputInformation bold content={event.content} />
      </div>
    </div>
  );
};

const NotificationTimelineEvent = ({
  event,
  displayFullDate,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.info}>
        <div className={classes.infoHeader}>
          <Date displayFullDate={displayFullDate} event={event} />
          <div className={classes.title}>
            <Author event={event} />
          </div>
        </div>
        <OutputInformation bold content={event.content} />
      </div>
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

const eventsByDateDivision = [
  {
    displayFullDate: false,
    getEventsByDate: ({ events }): Array<TimelineEvent> =>
      (
        filter(
          ({ date }) => dayjs(date).isToday(),
          events,
        ) as Array<TimelineEvent>
      ).sort(sortEventsByDate),
    label: 'Today',
  },
  {
    displayFullDate: false,
    getEventsByDate: ({ events }): Array<TimelineEvent> =>
      (
        filter(
          ({ date }) => dayjs(date).isYesterday(),
          events,
        ) as Array<TimelineEvent>
      ).sort(sortEventsByDate),
    label: 'Yesterday',
  },
  {
    displayFullDate: true,
    getEventsByDate: ({ events, locale }): Array<TimelineEvent> =>
      (
        filter(
          ({ date }) =>
            dayjs(date).isBetween(
              dayjs().locale(locale).weekday(-7),
              dayjs().subtract(2, 'day'),
            ),
          events,
        ) as Array<TimelineEvent>
      ).sort(sortEventsByDate),
    label: 'This week',
  },
];

export {
  TimelineEventByType,
  types,
  getTypeIds,
  TimelineIconByType,
  eventsByDateDivision,
  sortEventsByDate,
};
