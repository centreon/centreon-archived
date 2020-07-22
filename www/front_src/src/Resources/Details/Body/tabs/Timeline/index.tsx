import * as React from 'react';

import {
  useRequest,
  getData,
  ListingModel,
  ContentWithCircularLoading,
} from '@centreon/ui';

import { makeStyles, Paper, Typography } from '@material-ui/core';
import {
  isNil,
  groupBy,
  groupWith,
  equals,
  toPairs,
  reduceBy,
  merge,
  pipe,
  prop,
} from 'ramda';
import { getFormattedDate } from '../../../../dateTime';
import { ResourceEndpoints } from '../../../../models';
import EventChip from './Chip/Event';
import { TimelineEventByType } from './Event';
import { TimelineEvent as TimelineEventModel } from './models';

interface Props {
  endpoints: Pick<ResourceEndpoints, 'timeline'>;
}

type TimelineListing = ListingModel<TimelineEventModel>;

const useStyles = makeStyles((theme) => ({
  container: {
    width: '100%',
    height: '100%',
  },
  events: {
    display: 'grid',
    gridAutoFlow: 'row',
    padding: theme.spacing(1),
    gridGap: theme.spacing(1),
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

const TimelineTab = ({ endpoints }: Props): JSX.Element => {
  const classes = useStyles();

  const [timeline, setTimeline] = React.useState<TimelineListing>();

  const { timeline: timelineEndpoint } = endpoints;

  const { sendRequest, sending } = useRequest<TimelineListing>({
    request: getData,
  });

  React.useEffect(() => {
    sendRequest('http://localhost:5000/mock/timeline').then(setTimeline);
  }, []);

  const timelineResult = timeline?.result || [];

  const eventsByDate = reduceBy(
    (acc, event) => acc.concat(event),
    new Array<TimelineEventModel>(),
    pipe(prop('date'), getFormattedDate),
    timelineResult,
  );

  console.log(toPairs(eventsByDate));

  return (
    <div className={classes.container}>
      <ContentWithCircularLoading loading={isNil(timeline)} alignCenter>
        <div className={classes.events}>
          {toPairs(eventsByDate).map(
            ([date, events]): JSX.Element => {
              return (
                <div key={date} className={classes.events}>
                  <Typography variant="h6">{date}</Typography>

                  {events.map((event) => {
                    const { id, type } = event;

                    const TimelineEvent = TimelineEventByType[type];

                    return (
                      <Paper key={id} square className={classes.event}>
                        <TimelineEvent event={event} />
                      </Paper>
                    );
                  })}
                </div>
              );
            },
          )}
        </div>
      </ContentWithCircularLoading>
    </div>
  );
};

export default TimelineTab;
