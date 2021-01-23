import * as React from 'react';

import { prop, isNil } from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles, Chip, Typography } from '@material-ui/core';
import EventIcon from '@material-ui/icons/Event';
import CommentIcon from '@material-ui/icons/Comment';
import NotificationIcon from '@material-ui/icons/Notifications';

import { StatusChip, useLocaleDateTimeFormat } from '@centreon/ui';

import {
  labelEvent,
  labelComment,
  labelAcknowledgement,
  labelDowntime,
  labelBy,
  labelFrom,
  labelTo,
  labelNotificationSentTo,
  labelTries,
  labelNotification,
} from '../../../translatedLabels';
import DowntimeChip from '../../../Chip/Downtime';
import AcknowledgeChip from '../../../Chip/Acknowledge';
import truncate from '../../../truncate';

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
  info: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: 4,
  },
  title: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridAutoColumns: 'auto',
    gridGap: theme.spacing(2),
    justifyContent: 'flex-start',
    alignItems: 'center',
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

interface Props {
  event: TimelineEvent;
}

const Date = ({ event }: Props): JSX.Element => {
  const { toTime } = useLocaleDateTimeFormat();

  return <Typography variant="caption">{toTime(event.date)}</Typography>;
};

const Content = ({ event }: Props): JSX.Element => {
  const { content } = event;

  return <Typography variant="subtitle2">{truncate(content)}</Typography>;
};

const Author = ({ event }: Props): JSX.Element => {
  const suffix = event.contact ? `${labelBy} ${event.contact.name}` : '';

  return <Chip label={suffix} size="small" variant="outlined" />;
};

const EventTimelineEvent = ({ event }: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <EventIcon color="primary" />
      <div className={classes.info}>
        <div
          style={{
            display: 'grid',
            gridGap: 16,
            gridAutoFlow: 'column',
            gridTemplateColumns: 'minmax(auto, 60px) auto 1fr',
          }}
        >
          <Date event={event} />
          <StatusChip
            style={{
              height: 18,
              fontSize: '0.75rem',
            }}
            clickable={false}
            title={t(labelEvent)}
            severityCode={event.status?.severity_code as number}
            label={t(event.status?.name as string)}
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
        <Content event={event} />
      </div>
    </div>
  );
};

const CommentTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <CommentIcon color="primary" />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Author event={event} />
        </div>
        <Content event={event} />
      </div>
    </div>
  );
};

const AcknowledgeTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <AcknowledgeChip />
      <div className={classes.info}>
        <div
          style={{
            display: 'grid',
            gridGap: 16,
            gridAutoFlow: 'column',
            gridTemplateColumns: 'auto auto 1fr',
          }}
        >
          <Date event={event} />
          <div className={classes.title}>
            <Author event={event} />
          </div>
        </div>
        <Content event={event} />
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
      <DowntimeChip />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Author event={event} />
        </div>
        <Typography variant="caption">{getCaption()}</Typography>
        <Content event={event} />
      </div>
    </div>
  );
};

const NotificationTimelineEvent = ({ event }: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <NotificationIcon color="primary" />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Typography variant="h6">
            {`${t(labelNotificationSentTo)} ${event.contact?.name}`}
          </Typography>
        </div>
        <Content event={event} />
      </div>
    </div>
  );
};

const TimelineEventByType = {
  event: EventTimelineEvent,
  notification: NotificationTimelineEvent,
  comment: CommentTimelineEvent,
  acknowledgement: AcknowledgeTimelineEvent,
  downtime: DowntimeTimelineEvent,
};

export { TimelineEventByType, types, getTypeIds };
