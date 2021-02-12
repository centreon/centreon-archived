import * as React from 'react';

import { prop, isNil } from 'ramda';
import { useTranslation } from 'react-i18next';
import parse from 'html-react-parser';
import DOMPurify from 'dompurify';

import { makeStyles, Chip, Typography } from '@material-ui/core';
import EventIcon from '@material-ui/icons/Event';
import CommentIcon from '@material-ui/icons/Comment';
import NotificationIcon from '@material-ui/icons/Notifications';
import FaceIcon from '@material-ui/icons/Face';

import { StatusChip, useLocaleDateTimeFormat } from '@centreon/ui';

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
    gridGap: theme.spacing(1),
  },
  infoHeader: {
    display: 'grid',
    gridGap: theme.spacing(2),
    gridAutoFlow: 'column',
    gridTemplateColumns: 'minmax(80px, auto) auto 1fr',
    alignItems: 'start',
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
  chip: {
    height: 18,
    fontSize: theme.typography.pxToRem(12),
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

  return (
    <Typography variant="body2" style={{ fontWeight: 600 }}>
      {parse(DOMPurify.sanitize(truncate(content)))}
    </Typography>
  );
};

const Author = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  const contactName = event.contact?.name || '';

  return (
    <Chip
      className={classes.chip}
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
      <EventIcon aria-label={t(labelEvent)} color="primary" />
      <div className={classes.info}>
        <div className={classes.infoHeader}>
          <Date event={event} />
          <StatusChip
            classes={{ root: classes.chip }}
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
  const { t } = useTranslation();
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <CommentIcon aria-label={t(labelComment)} color="primary" />
      <div className={classes.info}>
        <div className={classes.infoHeader}>
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

const AcknowledgeTimelineEvent = ({ event }: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <AcknowledgeChip aria-label={t(labelAcknowledgement)} />
      <div className={classes.info}>
        <div className={classes.infoHeader}>
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
      <DowntimeChip aria-label={t(labelDowntime)} />
      <div className={classes.info}>
        <div className={classes.infoHeader}>
          <Typography variant="caption">{getCaption()}</Typography>
          <div className={classes.title}>
            <Author event={event} />
          </div>
        </div>
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
      <NotificationIcon aria-label={t(labelNotification)} color="primary" />
      <div className={classes.info}>
        <div className={classes.infoHeader}>
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

const TimelineEventByType = {
  event: EventTimelineEvent,
  notification: NotificationTimelineEvent,
  comment: CommentTimelineEvent,
  acknowledgement: AcknowledgeTimelineEvent,
  downtime: DowntimeTimelineEvent,
};

export { TimelineEventByType, types, getTypeIds };
