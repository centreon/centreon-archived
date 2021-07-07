import * as React from 'react';

import { prop, isNil } from 'ramda';
import { TFunction, useTranslation } from 'react-i18next';

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
  event: TimelineEvent;
}

const Date = ({ event }: Props): JSX.Element => {
  const { toTime } = useLocaleDateTimeFormat();

  return <Typography variant="caption">{toTime(event.date)}</Typography>;
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
      <div className={classes.info}>
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
    </div>
  );
};

const CommentTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.info}>
        <div className={classes.infoHeader}>
          <Date event={event} />
          <div className={classes.title}>
            <Author event={event} />
          </div>
        </div>
        <OutputInformation bold content={event.content} />
      </div>
    </div>
  );
};

const AcknowledgeTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.event}>
      <div className={classes.info}>
        <div className={classes.infoHeader}>
          <Date event={event} />
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

export { TimelineEventByType, types, getTypeIds, TimelineIconByType };
