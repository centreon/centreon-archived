import * as React from 'react';

import { makeStyles, Typography } from '@material-ui/core';
import EventIcon from '@material-ui/icons/Event';
import CommentIcon from '@material-ui/icons/Comment';

import { StatusChip } from '@centreon/ui';
import { gt } from 'ramda';

import { TimelineEvent } from './models';
import {
  labelEvent,
  labelComment,
  labelAcknowledgement,
  labelDowntime,
  labelBy,
  labelFrom,
  labelTo,
} from '../../../../translatedLabels';
import { getFormattedTime, getFormattedDateTime } from '../../../../dateTime';
import DowntimeChip from '../../../../Chip/Downtime';
import AcknowledgeChip from '../../../../Chip/Acknowledge';

const useStyles = makeStyles((theme) => ({
  info: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(0.5),
  },
  title: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridAutoColumns: 'auto',
    gridGap: theme.spacing(2),
    justifyContent: 'flex-start',
    alignItems: 'center',
  },
}));

interface Props {
  event: TimelineEvent;
}

const Date = ({ event }: Props): JSX.Element => (
  <Typography variant="caption">{getFormattedTime(event.date)}</Typography>
);

const truncate = (content: string): string => {
  const maxLength = 180;

  if (gt(content.length, maxLength)) {
    return `${content.substring(0, maxLength)}...`;
  }

  return content;
};
const Content = ({ event }: Props): JSX.Element => {
  const { content } = event;

  return <Typography variant="caption">{truncate(content)}</Typography>;
};

const EventTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <EventIcon color="primary" />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Typography variant="h6">{labelEvent}</Typography>
          <StatusChip
            severityCode={event.status?.severityCode as number}
            label={event.status?.name}
          />
        </div>
        <Content event={event} />
      </div>
      <Typography>{event.tries}</Typography>
    </>
  );
};

const CommentTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <CommentIcon color="primary" />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Typography variant="h6">{`${labelComment} ${labelBy} ${event.authorName}`}</Typography>
        </div>
        <Content event={event} />
      </div>
    </>
  );
};

const AcknowledgeTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <AcknowledgeChip />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Typography variant="h6">{`${labelAcknowledgement} ${labelBy} ${event.authorName}`}</Typography>
        </div>
        <Content event={event} />
      </div>
    </>
  );
};

const DowntimeTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <DowntimeChip />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Typography variant="h6">{`${labelDowntime} ${labelBy} ${event.authorName}`}</Typography>
        </div>
        <Typography variant="caption">
          {`${labelFrom} ${getFormattedDateTime(
            event.startDate,
          )} ${labelTo} ${getFormattedDateTime(event.endDate)}`}
        </Typography>
        <Content event={event} />
      </div>
    </>
  );
};

const TimelineEventByType = {
  event: EventTimelineEvent,
  notif: CommentTimelineEvent,
  ack: AcknowledgeTimelineEvent,
  downtime: DowntimeTimelineEvent,
};

export { TimelineEventByType };
