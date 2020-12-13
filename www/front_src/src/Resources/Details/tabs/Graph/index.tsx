import * as React from 'react';

import { path, isNil } from 'ramda';

import { Paper, Theme, makeStyles } from '@material-ui/core';

import { useRequest, ListingModel } from '@centreon/ui';

import PerformanceGraph from '../../../Graph/Performance';
import { TabProps } from '..';
import { listTimelineEvents } from '../Timeline/api';
import { TimelineEvent } from '../Timeline/models';
import { listTimelineEventsDecoder } from '../Timeline/api/decoders';
import useTimePeriod from '../../../Graph/Performance/TimePeriodSelect/useTimePeriod';
import TimePeriodSelect from '../../../Graph/Performance/TimePeriodSelect';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridTemplateRows: 'auto 1fr',
    gridRowGap: theme.spacing(2),
  },
  graphContainer: {
    display: 'grid',
    padding: theme.spacing(2, 1, 1),
    gridTemplateRows: '1fr',
  },
  graph: {
    margin: 'auto',
    height: '100%',
    width: '100%',
  },
}));

const GraphTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();

  const { sendRequest: sendGetTimelineRequest } = useRequest<
    ListingModel<TimelineEvent>
  >({
    request: listTimelineEvents,
    decoder: listTimelineEventsDecoder,
  });

  const [timeline, setTimeline] = React.useState<Array<TimelineEvent>>();

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
    getIntervalDates,
  } = useTimePeriod();

  const endpoint = path(['links', 'endpoints', 'performance_graph'], details);
  const timelineEndpoint = path<string>(
    ['links', 'endpoints', 'timeline'],
    details,
  );

  const retrieveTimeline = (): void => {
    if (isNil(timelineEndpoint)) {
      setTimeline([]);
      return;
    }

    const [start, end] = getIntervalDates();

    sendGetTimelineRequest({
      endpoint: timelineEndpoint,
      parameters: {
        limit: selectedTimePeriod.timelineEventsLimit,
        search: {
          conditions: [
            {
              field: 'date',
              values: {
                $gt: start,
                $lt: end,
              },
            },
          ],
        },
      },
    }).then(({ result }) => {
      setTimeline(result);
    });
  };

  React.useEffect(() => {
    if (isNil(endpoint)) {
      return;
    }

    retrieveTimeline();
  }, [endpoint, selectedTimePeriod]);

  const getEndpoint = (): string | undefined => {
    if (isNil(endpoint)) {
      return undefined;
    }

    return `${endpoint}${periodQueryParameters}`;
  };

  return (
    <div className={classes.container}>
      <TimePeriodSelect
        selectedTimePeriodId={selectedTimePeriod.id}
        onChange={changeSelectedTimePeriod}
      />
      <Paper className={classes.graphContainer}>
        <div className={classes.graph}>
          <PerformanceGraph
            endpoint={getEndpoint()}
            graphHeight={280}
            xAxisTickFormat={selectedTimePeriod.dateTimeFormat}
            toggableLegend
            timeline={timeline as Array<TimelineEvent>}
          />
        </div>
      </Paper>
    </div>
  );
};

export default GraphTab;
