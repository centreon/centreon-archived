import * as React from 'react';

import { makeStyles, Typography } from '@material-ui/core';
import EventIcon from '@material-ui/icons/Event';
import CommentIcon from '@material-ui/icons/Comment';
import AcknowledgeIcon from '@material-ui/icons/Person';

import { StatusChip } from '@centreon/ui';
import DowntimeIcon from '../../../../icons/Downtime';

import { TimelineEvent } from './models';
import { labelEvent, labelComment } from '../../../../translatedLabels';
import { getFormattedTime } from '../../../../dateTime';

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

const EventTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <EventIcon />
      <div className={classes.info}>
        <Typography variant="caption">
          {getFormattedTime(event.object.create_time)}
        </Typography>
        <div className={classes.title}>
          <Typography variant="h6">{labelEvent}</Typography>
          <StatusChip
            severityCode={event.object.severity_code as number}
            label={event.object.status}
          />
        </div>
        <Typography variant="caption">{event.object.output}</Typography>
      </div>
      <Typography>{event.object.tries}</Typography>
    </>
  );
};

const CommentTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <CommentIcon />
      <div className={classes.info}>
        <Typography variant="caption">
          {getFormattedTime(event.object.create_time)}
        </Typography>
        <div className={classes.title}>
          <Typography variant="h6">{labelComment}</Typography>
          <Typography>{event.object.notification_contact}</Typography>
        </div>
        <Typography variant="caption">{event.object.output}</Typography>
      </div>
      <Typography>{event.object.tries}</Typography>
    </>
  );
};

const TimelineEventByType = {
  L: EventTimelineEvent,
  C: CommentTimelineEvent,
};

export { TimelineEventByType };
