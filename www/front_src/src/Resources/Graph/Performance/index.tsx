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
  or,
  pathOr,
  propOr,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles, Typography, Theme } from '@material-ui/core';
import SaveAsImageIcon from '@material-ui/icons/esm/SaveAlt';
import { Skeleton } from '@material-ui/lab';

import {
  useRequest,
  getData,
  timeFormat,
  ContentWithCircularLoading,
  IconButton,
  useLocaleDateTimeFormat,
} from '@centreon/ui';

import { TimelineEvent } from '../../Details/tabs/Timeline/models';
import { Resource } from '../../models';
import { ResourceDetails } from '../../Details/models';
import { CommentParameters } from '../../Actions/api';
import {
  labelExportToPng,
  labelNoDataForThisPeriod,
} from '../../translatedLabels';
import {
  CustomTimePeriod,
  CustomTimePeriodProperty,
} from '../../Details/tabs/Graph/models';
import { useResourceContext } from '../../Context';

import Graph from './Graph';
import Legend from './Legend';
import LoadingSkeleton from './LoadingSkeleton';
import {
  GraphData,
  TimeValue,
  Line as LineModel,
  AdjustTimePeriodProps,
  Metric,
} from './models';
import { getTimeSeries, getLineData } from './timeSeries';
import useMetricsValue, { MetricsValueContext } from './Graph/useMetricsValue';
import { TimeShiftDirection } from './Graph/TimeShiftZones';
import exportToPng from './ExportableGraphWithTimeline/exportToPng';

interface Props {
  adjustTimePeriod?: (props: AdjustTimePeriodProps) => void;
  customTimePeriod?: CustomTimePeriod;
  displayEventAnnotations?: boolean;
  displayTitle?: boolean;
  endpoint?: string;
  graphHeight: number;
  isInViewport?: boolean;
  limitLegendRows?: boolean;
  onAddComment?: (commentParameters: CommentParameters) => void;
  resource: Resource | ResourceDetails;
  resourceDetailsUpdated?: boolean;
  timeline?: Array<TimelineEvent>;
  toggableLegend?: boolean;
  xAxisTickFormat?: string;
}

interface MakeStylesProps extends Pick<Props, 'graphHeight' | 'displayTitle'> {
  canAdjustTimePeriod: boolean;
}

const useStyles = makeStyles<Theme, MakeStylesProps>((theme) => ({
  container: {
    display: 'grid',
    flexDirection: 'column',
    gridGap: theme.spacing(0.5),
    gridTemplateRows: ({ graphHeight, displayTitle }): string =>
      `${displayTitle ? 'min-content' : ''} ${theme.spacing(
        2,
      )}px ${graphHeight}px auto`,
    height: '100%',
    justifyItems: 'center',
    width: 'auto',
  },
  exportToPngButton: {
    justifySelf: 'end',
  },
  graphHeader: {
    display: 'grid',
    gridTemplateColumns: '0.1fr 1fr 0.1fr',
    justifyItems: 'center',
    width: '100%',
  },
  graphTranslation: {
    columnGap: `${theme.spacing(1)}px`,
    display: 'grid',
    gridTemplateColumns: ({ canAdjustTimePeriod }) =>
      canAdjustTimePeriod ? 'min-content auto min-content' : 'auto',
    justifyContent: ({ canAdjustTimePeriod }) =>
      canAdjustTimePeriod ? 'space-between' : 'center',
    margin: theme.spacing(0, 1),
    width: '90%',
  },
  legend: {
    height: '100%',
    width: '100%',
  },
  loadingContainer: {
    height: theme.spacing(2),
    width: theme.spacing(2),
  },
  noDataContainer: {
    alignItems: 'center',
    display: 'flex',
    height: '100%',
    justifyContent: 'center',
  },
}));

const shiftRatio = 2;

const PerformanceGraph = ({
  endpoint,
  graphHeight,
  xAxisTickFormat = timeFormat,
  toggableLegend = false,
  timeline,
  resource,
  onAddComment,
  adjustTimePeriod,
  customTimePeriod,
  resourceDetailsUpdated = true,
  displayEventAnnotations = false,
  displayTitle = true,
  limitLegendRows,
  isInViewport = true,
}: Props): JSX.Element | null => {
  const classes = useStyles({
    canAdjustTimePeriod: not(isNil(adjustTimePeriod)),
    displayTitle,
    graphHeight,
  });
  const { t } = useTranslation();

  const [timeSeries, setTimeSeries] = React.useState<Array<TimeValue>>([]);
  const [lineData, setLineData] = React.useState<Array<LineModel>>();
  const [title, setTitle] = React.useState<string>();
  const [base, setBase] = React.useState<number>();
  const [exporting, setExporting] = React.useState<boolean>(false);
  const performanceGraphRef = React.useRef<HTMLDivElement | null>(null);
  const performanceGraphHeightRef = React.useRef<number>(0);

  const { selectedResourceId } = useResourceContext();

  const {
    sendRequest: sendGetGraphDataRequest,
    sending: sendingGetGraphDataRequest,
  } = useRequest<GraphData>({
    request: getData,
  });
  const metricsValueProps = useMetricsValue(isInViewport);
  const { toDateTime } = useLocaleDateTimeFormat();

  React.useEffect(() => {
    if (isNil(endpoint)) {
      return;
    }

    sendGetGraphDataRequest(endpoint).then((graphData) => {
      setTimeSeries(getTimeSeries(graphData));
      setBase(graphData.global.base);
      setTitle(graphData.global.title);
      const newLineData = getLineData(graphData);
      if (lineData) {
        setLineData(
          newLineData.map((line) => ({
            ...line,
            display: find(propEq('name', line.name), lineData)?.display ?? true,
          })),
        );
        return;
      }
      setLineData(newLineData);
    });
  }, [endpoint]);

  React.useEffect(() => {
    if (or(isNil(selectedResourceId), isNil(lineData))) {
      return;
    }
    setLineData(undefined);
  }, [selectedResourceId]);

  React.useEffect(() => {
    if (isInViewport && performanceGraphRef.current && lineData) {
      performanceGraphHeightRef.current =
        performanceGraphRef.current.clientHeight;
    }
  }, [isInViewport, lineData]);

  if (isNil(lineData) || isNil(timeline) || isNil(endpoint)) {
    return (
      <LoadingSkeleton
        displayTitleSkeleton={displayTitle}
        graphHeight={graphHeight}
      />
    );
  }

  if (lineData && not(isInViewport)) {
    return (
      <Skeleton
        height={performanceGraphHeightRef.current}
        variant="rect"
        width="100%"
      />
    );
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

  const getShiftedDate = ({ property, direction, timePeriod }): Date => {
    const adjustTimePeriodProps =
      (timePeriod.end.getTime() - timePeriod.start.getTime()) / shiftRatio;

    return new Date(
      add(
        prop(property, timePeriod).getTime(),
        equals(direction, TimeShiftDirection.backward)
          ? negate(adjustTimePeriodProps)
          : adjustTimePeriodProps,
      ),
    );
  };

  const shiftTime = (direction: TimeShiftDirection) => {
    if (isNil(customTimePeriod)) {
      return;
    }

    adjustTimePeriod?.({
      end: getShiftedDate({
        direction,
        property: CustomTimePeriodProperty.end,
        timePeriod: customTimePeriod,
      }),
      start: getShiftedDate({
        direction,
        property: CustomTimePeriodProperty.start,
        timePeriod: customTimePeriod,
      }),
    });
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

  const timeTick = pathOr(
    '',
    ['metricsValue', 'timeValue', 'timeTick'],
    metricsValueProps,
  );

  const metricsValue = prop('metricsValue', metricsValueProps);

  const metrics = propOr([] as Array<Metric>, 'metrics', metricsValue);

  const containsMetrics = not(isNil(metrics)) && not(isEmpty(metrics));

  return (
    <MetricsValueContext.Provider value={metricsValueProps}>
      <div
        className={classes.container}
        ref={
          performanceGraphRef as React.MutableRefObject<HTMLDivElement | null>
        }
      >
        {displayTitle && (
          <div className={classes.graphHeader}>
            <div />
            <Typography color="textPrimary" variant="body1">
              {title}
            </Typography>
            <div className={classes.exportToPngButton}>
              <ContentWithCircularLoading
                alignCenter={false}
                loading={exporting}
                loadingIndicatorSize={16}
              >
                <IconButton
                  disableTouchRipple
                  disabled={isNil(timeline)}
                  title={t(labelExportToPng)}
                  onClick={convertToPng}
                >
                  <SaveAsImageIcon style={{ fontSize: 18 }} />
                </IconButton>
              </ContentWithCircularLoading>
            </div>
          </div>
        )}

        <div>
          {timeTick && containsMetrics && (
            <Typography variant="body1">{toDateTime(timeTick)}</Typography>
          )}
        </div>

        <ParentSize>
          {({ width, height }): JSX.Element => (
            <Graph
              applyZoom={adjustTimePeriod}
              base={base as number}
              canAdjustTimePeriod={not(isNil(adjustTimePeriod))}
              containsMetrics={containsMetrics}
              displayEventAnnotations={displayEventAnnotations}
              height={height}
              lines={displayedLines}
              loading={
                not(resourceDetailsUpdated) && sendingGetGraphDataRequest
              }
              resource={resource}
              shiftTime={shiftTime}
              timeSeries={timeSeries}
              timeline={timeline}
              width={width}
              xAxisTickFormat={xAxisTickFormat}
              onAddComment={onAddComment}
            />
          )}
        </ParentSize>
        <div className={classes.legend}>
          <Legend
            base={base as number}
            limitLegendRows={limitLegendRows}
            lines={sortedLines}
            toggable={toggableLegend}
            onClearHighlight={clearHighlight}
            onHighlight={highlightLine}
            onSelect={selectMetricLine}
            onToggle={toggleMetricLine}
          />
        </div>
      </div>
    </MetricsValueContext.Provider>
  );
};

export default PerformanceGraph;
