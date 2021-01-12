import * as React from 'react';

import { path, isNil } from 'ramda';
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
  IconButton,
  useRequest,
  ListingModel,
  ContentWithCircularLoading,
} from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import {
  labelDisplayEvents,
  labelExportToPng,
} from '../../../translatedLabels';
import { TimelineEvent } from '../../../Details/tabs/Timeline/models';
import { listTimelineEvents } from '../../../Details/tabs/Timeline/api';
import { listTimelineEventsDecoder } from '../../../Details/tabs/Timeline/api/decoders';
import PerformanceGraph from '..';
import { TimePeriod } from '../../../Details/tabs/Graph/models';
import { Resource } from '../../../models';
import { ResourceDetails } from '../../../Details/models';

import exportToPng from './exportToPng';

const useStyles = makeStyles((theme: Theme) => ({
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
    width: '100%',
  },
}));

interface Props {
  resource?: Resource | ResourceDetails;
  selectedTimePeriod: TimePeriod;
  getIntervalDates: () => [string, string];
  periodQueryParameters: string;
  graphHeight: number;
  onTooltipDisplay?: (x?: number) => void;
  tooltipX?: number;
}

const ExportablePerformanceGraphWithTimeline = ({
  resource,
  selectedTimePeriod,
  getIntervalDates,
  periodQueryParameters,
  graphHeight,
  onTooltipDisplay,
  tooltipX,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { alias } = useUserContext();

  const performanceGraphRef = React.useRef<HTMLDivElement>();
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

  const endpoint = path(['links', 'endpoints', 'performance_graph'], resource);
  const timelineEndpoint = path<string>(
    ['links', 'endpoints', 'timeline'],
    resource,
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

  const changeEventAnnotationsActive = (
    event: React.ChangeEvent<HTMLInputElement>,
  ): void => {
    setEventAnnotationsActive(event.target.checked);
  };

  const convertToPng = (): void => {
    setExporting(true);
    exportToPng({
      element: performanceGraphRef.current as HTMLElement,
      title: `${resource?.name}-performance`,
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
        className={classes.graph}
        ref={performanceGraphRef as React.RefObject<HTMLDivElement>}
      >
        <PerformanceGraph
          endpoint={getEndpoint()}
          graphHeight={graphHeight}
          xAxisTickFormat={selectedTimePeriod.dateTimeFormat}
          toggableLegend
          resource={resource as Resource}
          eventAnnotationsActive={eventAnnotationsActive}
          timeline={timeline}
          onAddComment={addCommentToTimeline}
          onTooltipDisplay={onTooltipDisplay}
          tooltipX={tooltipX}
        />
      </div>
    </Paper>
  );
};

export default ExportablePerformanceGraphWithTimeline;
