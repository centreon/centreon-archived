import * as React from 'react';

import { pick, map, path, isNil } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  Paper,
  Theme,
  makeStyles,
  FormControlLabel,
  Switch,
  Typography,
} from '@material-ui/core';
import SaveAsImageIcon from '@material-ui/icons/SaveAlt';

import {
  SelectField,
  IconButton,
  useRequest,
  ListingModel,
  ContentWithCircularLoading,
} from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import PerformanceGraph from '../../../Graph/Performance';
import { TabProps } from '..';
import { ResourceDetails } from '../../models';
import {
  labelDisplayEvents,
  labelExportToPng,
} from '../../../translatedLabels';
import { TimelineEvent } from '../Timeline/models';
import { listTimelineEvents } from '../Timeline/api';
import { listTimelineEventsDecoder } from '../Timeline/api/decoders';

import {
  timePeriods,
  getTimePeriodById,
  last24hPeriod,
  TimePeriod,
} from './models';
import exportToPng from './exportToPng';

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
  exportToPngButton: {
    display: 'flex',
    justifyContent: 'space-between',
    margin: theme.spacing(0, 1, 1, 2),
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
  const performanceGraphRef = React.useRef<HTMLDivElement>();
  const { alias } = useUserContext();

  const { sendRequest: sendGetTimelineRequest } = useRequest<
    ListingModel<TimelineEvent>
  >({
    request: listTimelineEvents,
    decoder: listTimelineEventsDecoder,
  });

  const [eventAnnotationsActive, setEventAnnotationsActive] = React.useState(
    false,
  );
  const [timeline, setTimeline] = React.useState<Array<TimelineEvent>>();
  const [exporting, setExporting] = React.useState(false);

  const [
    selectedTimePeriod,
    setSelectedTimePeriod,
  ] = React.useState<TimePeriod>(defaultTimePeriod);

  const [endpoint, setEndpoint] = React.useState<string>();

  const translatedTimePeriodSelectOptions = timePeriodSelectOptions.map(
    (timePeriod) => ({
      ...timePeriod,
      name: t(timePeriod.name),
    }),
  );

  const baseEndpoint = path(
    ['links', 'endpoints', 'performance_graph'],
    details,
  );
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
    if (isNil(details)) {
      return;
    }

    const [start, end] = getIntervalDates(selectedTimePeriod);
    const periodQueryParams = `?start=${start}&end=${end}`;
    setEndpoint(`${baseEndpoint}${periodQueryParams}`);
    retrieveTimeline();
  }, [baseEndpoint, selectedTimePeriod, details]);

  const changeSelectedPeriod = (event): void => {
    const timePeriodId = event.target.value;
    const timePeriod = getTimePeriodById(timePeriodId);

    setSelectedTimePeriod(timePeriod);
  };

  const changeEventAnnotationsActive = (
    event: React.ChangeEvent<HTMLInputElement>,
  ): void => {
    setEventAnnotationsActive(event.target.checked);
  };

  const convertToPng = (): void => {
    setExporting(true);
    exportToPng({
      element: performanceGraphRef.current as HTMLElement,
      title: `${details?.name}-performance`,
    }).finally(() => {
      setExporting(false);
    });
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
        <div className={classes.exportToPngButton}>
          <FormControlLabel
            disabled={isNil(timeline)}
            control={
              <Switch
                color="primary"
                size="small"
                onChange={changeEventAnnotationsActive}
              />
            }
            label={
              <Typography variant="body2">{t(labelDisplayEvents)}</Typography>
            }
          />
          <ContentWithCircularLoading
            loading={exporting}
            loadingIndicatorSize={16}
            alignCenter={false}
          >
            <IconButton
              disabled={isNil(timeline)}
              title={t(labelExportToPng)}
              onClick={convertToPng}
            >
              <SaveAsImageIcon style={{ fontSize: 18 }} />
            </IconButton>
          </ContentWithCircularLoading>
        </div>
        <div
          className={`${classes.graph} ${classes.performance}`}
          ref={performanceGraphRef as React.RefObject<HTMLDivElement>}
        >
          <PerformanceGraph
            endpoint={endpoint}
            graphHeight={280}
            xAxisTickFormat={selectedTimePeriod.dateTimeFormat}
            toggableLegend
            resource={details as ResourceDetails}
            eventAnnotationsActive={eventAnnotationsActive}
            timeline={timeline}
            onAddComment={addCommentToTimeline}
          />
        </div>
      </Paper>
    </div>
  );
};

export default GraphTab;
