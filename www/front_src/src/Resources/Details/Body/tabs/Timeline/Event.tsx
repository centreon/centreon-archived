import * as React from 'react';

import { makeStyles, Typography } from '@material-ui/core';

import { StatusChip } from '@centreon/ui';

import { TimelineEvent } from './models';
import EventChip from './Chip/Event';
import { labelEvent } from '../../../../translatedLabels';
import { getFormattedTime } from '../../../../dateTime';

const useStyles = makeStyles((theme) => ({
  info: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(0.5),
  },
  eventLabelAndStatus: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridTemplateColumns: 'auto auto',
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
      <EventChip />
      <div className={classes.info}>
        <Typography variant="caption">
          {getFormattedTime(event.object.create_time)}
        </Typography>
        <div className={classes.eventLabelAndStatus}>
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

const TimelineEventByType = {
  L: EventTimelineEvent,
};

export { TimelineEventByType };
