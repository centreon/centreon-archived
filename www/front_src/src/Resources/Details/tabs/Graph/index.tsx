import * as React from 'react';

import { pick, map, path, isNil } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Paper, Theme, makeStyles } from '@material-ui/core';

import { SelectField, useRequest, ListingModel } from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import PerformanceGraph from '../../../Graph/Performance';
import { TabProps } from '..';
import { listTimelineEvents } from '../Timeline/api';
import { TimelineEvent } from '../Timeline/models';
import { listTimelineEventsDecoder } from '../Timeline/api/decoders';
import { ResourceDetails } from '../../models';

import {
  timePeriods,
  getTimePeriodById,
  last24hPeriod,
  TimePeriod,
} from './models';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    display: 'grid',
    gridTemplateRows: 'auto 1fr',
    gridRowGap: theme.spacing(2),
  },
  header: {
    padding: theme.spacing(2),
  },
  periodSelect: {
    width: 250,
  },
  graphContainer: {
    display: 'grid',
    padding: theme.spacing(2, 1, 1),
    gridTemplateRows: '1fr',
  },
  graph: {
    margin: 'auto',
    height: '100%',
  },
  performance: {
    width: '100%',
  },
  status: {
    marginTop: theme.spacing(2),
    width: '100%',
  },
}));

const timePeriodSelectOptions = map(pick(['id', 'name']), timePeriods);

const defaultTimePeriod = last24hPeriod;

const GraphTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { alias } = useUserContext();

  const { sendRequest: sendGetTimelineRequest } = useRequest<
    ListingModel<TimelineEvent>
  >({
    request: listTimelineEvents,
    decoder: listTimelineEventsDecoder,
  });

  const [timeline, setTimeline] = React.useState<Array<TimelineEvent>>();

  const [
    selectedTimePeriod,
    setSelectedTimePeriod,
  ] = React.useState<TimePeriod>(defaultTimePeriod);

  const translatedTimePeriodSelectOptions = timePeriodSelectOptions.map(
    (timePeriod) => ({
      ...timePeriod,
      name: t(timePeriod.name),
    }),
  );

  const endpoint = path(['links', 'endpoints', 'performance_graph'], details);
  const timelineEndpoint = path<string>(
    ['links', 'endpoints', 'timeline'],
    details,
  );

  const getIntervalDates = (timePeriod): Array<string> => {
    return [
      timePeriod.getStart().toISOString(),
      new Date(Date.now()).toISOString(),
    ];
  };

  const retrieveTimeline = (): void => {
    if (isNil(timelineEndpoint)) {
      setTimeline([]);
      return;
    }

    const [start, end] = getIntervalDates(selectedTimePeriod);

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

  const getGraphQueryParameters = (timePeriod): string => {
    const [start, end] = getIntervalDates(timePeriod);

    return `?start=${start}&end=${end}`;
  };

  const [periodQueryParams, setPeriodQueryParams] = React.useState(
    getGraphQueryParameters(selectedTimePeriod),
  );

  const changeSelectedPeriod = (event): void => {
    const timePeriodId = event.target.value;
    const timePeriod = getTimePeriodById(timePeriodId);

    setSelectedTimePeriod(timePeriod);

    const queryParamsForSelectedPeriodId = getGraphQueryParameters(timePeriod);
    setPeriodQueryParams(queryParamsForSelectedPeriodId);
  };

  const getEndpoint = (): string | undefined => {
    if (isNil(endpoint)) {
      return undefined;
    }

    return `${endpoint}${periodQueryParams}`;
  };

  const addCommentToTimeline = ({ date, comment }): void => {
    setTimeline([
      ...(timeline as Array<TimelineEvent>),
      {
        id: Math.random(),
        type: 'comment',
        date,
        content: comment,
        contact: { name: alias },
      },
    ]);
  };

  return (
    <div className={classes.container}>
      <Paper className={classes.header}>
        <SelectField
          className={classes.periodSelect}
          options={translatedTimePeriodSelectOptions}
          selectedOptionId={selectedTimePeriod.id}
          onChange={changeSelectedPeriod}
        />
      </Paper>
      <Paper className={classes.graphContainer}>
        <div className={`${classes.graph} ${classes.performance}`}>
          <PerformanceGraph
            endpoint={getEndpoint()}
            graphHeight={280}
            xAxisTickFormat={selectedTimePeriod.dateTimeFormat}
            toggableLegend
            resource={details as ResourceDetails}
            timeline={timeline as Array<TimelineEvent>}
            onAddComment={addCommentToTimeline}
          />
        </div>
      </Paper>
    </div>
  );
};

export default GraphTab;
