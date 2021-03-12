import * as React from 'react';

import { ParentSize } from '@visx/visx';
import {
  map,
  prop,
  propEq,
  find,
  reject,
  sortBy,
  isEmpty,
  isNil,
  head,
  equals,
  pipe,
  not,
  add,
  negate,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  makeStyles,
  Typography,
  Theme,
  CircularProgress,
  useTheme,
} from '@material-ui/core';
import ArrowBackIosIcon from '@material-ui/icons/ArrowBackIos';
import ArrowForwardIosIcon from '@material-ui/icons/ArrowForwardIos';

import { useRequest, getData, timeFormat, IconButton } from '@centreon/ui';

import { TimelineEvent } from '../../Details/tabs/Timeline/models';
import { Resource } from '../../models';
import { ResourceDetails } from '../../Details/models';
import { CommentParameters } from '../../Actions/api';
import { labelNoDataForThisPeriod } from '../../translatedLabels';
import { CustomTimePeriod } from '../../Details/tabs/Graph/models';

import Graph, { TranslationDirection } from './Graph';
import Legend from './Legend';
import LoadingSkeleton from './LoadingSkeleton';
import {
  GraphData,
  TimeValue,
  Line as LineModel,
  NavigateInGraphProps,
} from './models';
import { getTimeSeries, getLineData } from './timeSeries';

interface Props {
  endpoint?: string;
  xAxisTickFormat?: string;
  graphHeight: number;
  toggableLegend?: boolean;
  eventAnnotationsActive?: boolean;
  resource: Resource | ResourceDetails;
  timeline?: Array<TimelineEvent>;
  onAddComment?: (commentParameters: CommentParameters) => void;
  tooltipPosition?: [number, number];
  onTooltipDisplay?: (position?: [number, number]) => void;
  navigateInGraph?: (props: NavigateInGraphProps) => void;
  customTimePeriod?: CustomTimePeriod;
}

interface MakeStylesProps extends Pick<Props, 'graphHeight'> {
  canNavigateInGraph: boolean;
}

const useStyles = makeStyles<Theme, MakeStylesProps>((theme) => ({
  container: {
    display: 'grid',
    flexDirection: 'column',
    gridTemplateRows: ({ graphHeight }): string => `auto ${graphHeight}px auto`,
    gridGap: theme.spacing(1),
    height: '100%',
    justifyItems: 'center',
    width: 'auto',
  },
  noDataContainer: {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    height: '100%',
  },
  legend: {
    display: 'flex',
    flexWrap: 'wrap',
    justifyContent: 'center',
    alignItems: 'center',
    width: '100%',
  },
  graphHeader: {
    display: 'grid',
    gridTemplateColumns: 'auto auto',
    columnGap: `${theme.spacing(3)}px`,
  },
  graphTranslation: {
    display: 'grid',
    gridTemplateColumns: ({ canNavigateInGraph }) =>
      canNavigateInGraph ? 'min-content auto min-content' : 'auto',
    columnGap: `${theme.spacing(1)}px`,
    width: '90%',
    justifyContent: ({ canNavigateInGraph }) =>
      canNavigateInGraph ? 'space-between' : 'center',
    margin: theme.spacing(0, 1),
  },
  loadingContainer: {
    width: theme.spacing(2),
    height: theme.spacing(2),
  },
}));

const translationRatio = 2;

const PerformanceGraph = ({
  endpoint,
  graphHeight,
  xAxisTickFormat = timeFormat,
  toggableLegend = false,
  eventAnnotationsActive = false,
  timeline,
  tooltipPosition,
  onTooltipDisplay,
  resource,
  onAddComment,
  navigateInGraph,
  customTimePeriod,
}: Props): JSX.Element | null => {
  const classes = useStyles({
    graphHeight,
    canNavigateInGraph: not(isNil(navigateInGraph)),
  });
  const { t } = useTranslation();
  const theme = useTheme();

  const [timeSeries, setTimeSeries] = React.useState<Array<TimeValue>>([]);
  const [lineData, setLineData] = React.useState<Array<LineModel>>();
  const [title, setTitle] = React.useState<string>();
  const [base, setBase] = React.useState<number>();
  const [navigatingInGraph, setNavigatingInGraph] = React.useState<boolean>(
    false,
  );

  const {
    sendRequest: sendGetGraphDataRequest,
    sending: sendingGetGraphDataRequest,
  } = useRequest<GraphData>({
    request: getData,
  });

  React.useEffect(() => {
    if (isNil(endpoint)) {
      return;
    }

    if (not(navigatingInGraph)) {
      setLineData(undefined);
    }

    sendGetGraphDataRequest(endpoint).then((graphData) => {
      setTimeSeries(getTimeSeries(graphData));
      setLineData(getLineData(graphData));
      setTitle(graphData.global.title);
      setBase(graphData.global.base);
      setNavigatingInGraph(false);
    });
  }, [endpoint]);

  if (isNil(lineData) || isNil(timeline) || isNil(endpoint)) {
    return <LoadingSkeleton graphHeight={graphHeight} />;
  }

  if (isEmpty(timeSeries) || isEmpty(lineData)) {
    return (
      <div className={classes.noDataContainer}>
        <Typography align="center" variant="body1">
          {t(labelNoDataForThisPeriod)}
        </Typography>
      </div>
    );
  }

  const sortedLines = sortBy(prop('name'), lineData);
  const displayedLines = reject(propEq('display', false), sortedLines);

  const getLineByMetric = (metric): LineModel => {
    return find(propEq('metric', metric), lineData) as LineModel;
  };

  const toggleMetricLine = (metric): void => {
    const line = getLineByMetric(metric);

    setLineData([
      ...reject(propEq('metric', metric), lineData),
      { ...line, display: !line.display },
    ]);
  };

  const highlightLine = (metric): void => {
    const fadedLines = map((line) => ({ ...line, highlight: false }), lineData);

    setLineData([
      ...reject(propEq('metric', metric), fadedLines),
      { ...getLineByMetric(metric), highlight: true },
    ]);
  };

  const clearHighlight = (): void => {
    setLineData(map((line) => ({ ...line, highlight: undefined }), lineData));
  };

  const selectMetricLine = (metric: string): void => {
    const metricLine = getLineByMetric(metric);

    const isLineDisplayed = pipe(head, equals(metricLine))(displayedLines);
    const isOnlyLineDisplayed = displayedLines.length === 1 && isLineDisplayed;

    if (isOnlyLineDisplayed || isEmpty(displayedLines)) {
      setLineData(
        map(
          (line) => ({
            ...line,
            display: true,
          }),
          lineData,
        ),
      );

      return;
    }

    setLineData(
      map(
        (line) => ({
          ...line,
          display: equals(line, metricLine),
        }),
        lineData,
      ),
    );
  };

  const displayZoomLoader = (props: NavigateInGraphProps) => {
    setNavigatingInGraph(true);
    navigateInGraph?.(props);
  };

  const translate = (direction: TranslationDirection) => {
    if (isNil(customTimePeriod)) {
      return;
    }
    setNavigatingInGraph(true);
    const timestampToTranslate =
      (customTimePeriod.end.getTime() - customTimePeriod.start.getTime()) /
      translationRatio;

    navigateInGraph?.({
      start: new Date(
        add(
          customTimePeriod.start.getTime(),
          equals(direction, TranslationDirection.backward)
            ? negate(timestampToTranslate)
            : timestampToTranslate,
        ),
      ),
      end: new Date(
        add(
          customTimePeriod.end.getTime(),
          equals(direction, TranslationDirection.backward)
            ? negate(timestampToTranslate)
            : timestampToTranslate,
        ),
      ),
    });
  };

  return (
    <div className={classes.container}>
      <div className={classes.graphHeader}>
        <Typography variant="body1" color="textPrimary" align="center">
          {title}
        </Typography>
        <div className={classes.loadingContainer}>
          {sendingGetGraphDataRequest && (
            <CircularProgress size={theme.spacing(2)} />
          )}
        </div>
      </div>

      <ParentSize>
        {({ width, height }): JSX.Element => (
          <Graph
            width={width}
            height={height}
            timeSeries={timeSeries}
            lines={displayedLines}
            base={base as number}
            xAxisTickFormat={xAxisTickFormat}
            timeline={timeline}
            onTooltipDisplay={onTooltipDisplay}
            tooltipPosition={tooltipPosition}
            resource={resource}
            onAddComment={onAddComment}
            eventAnnotationsActive={eventAnnotationsActive}
            applyZoom={displayZoomLoader}
            translate={translate}
            sendingGetGraphDataRequest={sendingGetGraphDataRequest}
          />
        )}
      </ParentSize>
      <div className={classes.legend}>
        <Legend
          lines={sortedLines}
          onToggle={toggleMetricLine}
          onSelect={selectMetricLine}
          toggable={toggableLegend}
          onHighlight={highlightLine}
          onClearHighlight={clearHighlight}
        />
      </div>
    </div>
  );
};

export default PerformanceGraph;
