import * as React from 'react';

import {
  equals,
  isNil,
  isEmpty,
  identity,
  min,
  max,
  not,
  lt,
  gte,
  path,
} from 'ramda';
import {
  Line,
  Bar,
  scaleTime,
  scaleLinear,
  Group,
  GridRows,
  GridColumns,
  useTooltip,
  useTooltipInPortal,
  localPoint,
} from '@visx/visx';
import { bisector } from 'd3-array';
import { ScaleLinear } from 'd3-scale';
import { useTranslation } from 'react-i18next';

import {
  Button,
  ClickAwayListener,
  makeStyles,
  Paper,
  Typography,
  Theme,
  fade,
  useTheme,
  CircularProgress,
} from '@material-ui/core';
import { grey } from '@material-ui/core/colors';

import { dateTimeFormat, useLocaleDateTimeFormat } from '@centreon/ui';

import {
  TimeValue,
  Line as LineModel,
  GraphOptionId,
  AdjustTimePeriodProps,
} from '../models';
import {
  getTime,
  getMin,
  getMax,
  getLineForMetric,
  getDates,
  getUnits,
  getMetricValuesForUnit,
  getMetrics,
  getMetricValuesForLines,
  getSortedStackedLines,
  getStackedMetricValues,
  hasUnitStackedLines,
} from '../timeSeries';
import Lines from '../Lines';
import { labelAddComment } from '../../../translatedLabels';
import { TimelineEvent } from '../../../Details/tabs/Timeline/models';
import { Resource } from '../../../models';
import { ResourceDetails } from '../../../Details/models';
import { CommentParameters } from '../../../Actions/api';
import useAclQuery from '../../../Actions/Resource/aclQuery';
import { TabBounds, TabContext } from '../../../Details';
import memoizeComponent from '../../../memoizedComponent';
import {
  defaultGraphOptions,
  useGraphOptionsContext,
} from '../ExportableGraphWithTimeline/useGraphOptions';

import AddCommentForm from './AddCommentForm';
import Annotations from './Annotations';
import Axes from './Axes';
import { AnnotationsContext } from './Context';
import useAnnotations from './useAnnotations';
import TimeShiftZones, {
  TimeShiftContext,
  TimeShiftDirection,
} from './TimeShiftZones';
import { useMetricsValueContext } from './useMetricsValue';

const propsAreEqual = (prevProps, nextProps): boolean =>
  equals(prevProps, nextProps);

const MemoizedAxes = React.memo(Axes, propsAreEqual);
const MemoizedBar = React.memo(Bar, propsAreEqual);
const MemoizedGridColumns = React.memo(GridColumns, propsAreEqual);
const MemoizedGridRows = React.memo(GridRows, propsAreEqual);
const MemoizedLines = React.memo(Lines, propsAreEqual);
const MemoizedAnnotations = React.memo(Annotations, propsAreEqual);

const margin = { top: 30, right: 45, bottom: 30, left: 45 };

const commentTooltipWidth = 165;

interface Props {
  width: number;
  height: number;
  timeSeries: Array<TimeValue>;
  base: number;
  lines: Array<LineModel>;
  xAxisTickFormat: string;
  tooltipPosition?: [number, number];
  onTooltipDisplay?: (tooltipPosition?: [number, number]) => void;
  timeline?: Array<TimelineEvent>;
  resource: Resource | ResourceDetails;
  onAddComment?: (commentParameters: CommentParameters) => void;
}

const useStyles = makeStyles<Theme, Pick<Props, 'onAddComment'>>((theme) => ({
  container: {
    position: 'relative',
  },
  overlay: {
    cursor: ({ onAddComment }): string =>
      isNil(onAddComment) ? 'normal' : 'crosshair',
  },
  tooltip: {
    padding: 12,
    zIndex: theme.zIndex.tooltip,
  },
  addCommentTooltip: {
    position: 'absolute',
    fontSize: 10,
    display: 'grid',
    gridAutoFlow: 'row',
    justifyItems: 'center',
    padding: theme.spacing(0.5),
  },
  addCommentButton: {
    fontSize: 10,
  },
  graphLoader: {
    position: 'absolute',
    width: '100%',
    height: '100%',
    backgroundColor: fade(theme.palette.common.white, 0.5),
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
  },
}));

interface ZoomBoundaries {
  start: number;
  end: number;
}

interface GraphContentProps {
  width: number;
  height: number;
  timeSeries: Array<TimeValue>;
  base: number;
  lines: Array<LineModel>;
  xAxisTickFormat: string;
  timeline?: Array<TimelineEvent>;
  tooltipPosition?: [number, number];
  resource: Resource | ResourceDetails;
  addCommentTooltipLeft?: number;
  addCommentTooltipTop?: number;
  addCommentTooltipOpen: boolean;
  onAddComment?: (commentParameters: CommentParameters) => void;
  onTooltipDisplay?: (position?: [number, number]) => void;
  hideAddCommentTooltip: () => void;
  showAddCommentTooltip: (args) => void;
  format: (parameters) => string;
  applyZoom?: (props: AdjustTimePeriodProps) => void;
  shiftTime?: (direction: TimeShiftDirection) => void;
  loading: boolean;
  canAdjustTimePeriod: boolean;
}

const getScale = ({
  values,
  height,
  stackedValues,
}): ScaleLinear<number, number> => {
  const minValue = min(getMin(values), getMin(stackedValues));
  const maxValue = max(getMax(values), getMax(stackedValues));

  const upperRangeValue = minValue === maxValue && maxValue === 0 ? height : 0;

  return scaleLinear<number>({
    domain: [minValue, maxValue],
    nice: true,
    range: [height, upperRangeValue],
  });
};

const GraphContent = ({
  width,
  height,
  timeSeries,
  base,
  lines,
  xAxisTickFormat,
  timeline,
  tooltipPosition,
  resource,
  addCommentTooltipLeft,
  addCommentTooltipTop,
  addCommentTooltipOpen,
  onTooltipDisplay,
  onAddComment,
  hideAddCommentTooltip,
  showAddCommentTooltip,
  format,
  applyZoom,
  shiftTime,
  loading,
  canAdjustTimePeriod,
}: GraphContentProps): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles({ onAddComment });

  const [addingComment, setAddingComment] = React.useState(false);
  const [commentDate, setCommentDate] = React.useState<Date>();
  const [zoomPivotPosition, setZoomPivotPosition] = React.useState<
    number | null
  >(null);
  const [
    zoomBoundaries,
    setZoomBoundaries,
  ] = React.useState<ZoomBoundaries | null>(null);
  const { canComment } = useAclQuery();

  const theme = useTheme();
  const [isMouseOver, setIsMouseOver] = React.useState(false);

  const { containerRef, containerBounds, TooltipInPortal } = useTooltipInPortal(
    {
      detectBounds: true,
      scroll: true,
    },
  );

  const context = React.useContext<TabBounds>(TabContext);
  const graphOptions =
    useGraphOptionsContext()?.graphOptions || defaultGraphOptions;

  const {
    changeMetricsValue,
    metricsValue,
    hideTooltip,
    tooltipData,
    tooltipLeft,
    tooltipTop,
    tooltipOpen,
  } = useMetricsValueContext();

  const displayTooltipValues = path(
    [GraphOptionId.displayTooltips, 'value'],
    graphOptions,
  );
  const displayEventAnnotations = path(
    [GraphOptionId.displayEvents, 'value'],
    graphOptions,
  );

  const graphWidth = width > 0 ? width - margin.left - margin.right : 0;
  const graphHeight = height > 0 ? height - margin.top - margin.bottom : 0;

  const annotations = useAnnotations(graphWidth);

  const hideAddCommentTooltipOnEspcapePress = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') {
      hideAddCommentTooltip();
    }
  };

  React.useEffect(() => {
    document.addEventListener(
      'keydown',
      hideAddCommentTooltipOnEspcapePress,
      false,
    );

    return (): void => {
      document.removeEventListener(
        'keydown',
        hideAddCommentTooltipOnEspcapePress,
        false,
      );
    };
  }, []);

  const xScale = React.useMemo(
    () =>
      scaleTime<number>({
        range: [0, graphWidth],
        domain: [
          getMin(timeSeries.map(getTime)),
          getMax(timeSeries.map(getTime)),
        ],
      }),
    [graphWidth, timeSeries],
  );

  const [firstUnit, secondUnit, thirdUnit] = getUnits(lines);

  const leftScale = React.useMemo(() => {
    const values = isNil(thirdUnit)
      ? getMetricValuesForUnit({ lines, timeSeries, unit: firstUnit })
      : getMetricValuesForLines({ lines, timeSeries });

    const firstUnitHasStackedLines =
      isNil(thirdUnit) && not(isNil(firstUnit))
        ? hasUnitStackedLines({ lines, unit: firstUnit })
        : false;

    const stackedValues = firstUnitHasStackedLines
      ? getStackedMetricValues({
          lines: getSortedStackedLines(lines),
          timeSeries,
        })
      : [0];

    return getScale({ height: graphHeight, values, stackedValues });
  }, [timeSeries, lines, firstUnit, graphHeight]);

  const rightScale = React.useMemo(() => {
    const values = getMetricValuesForUnit({
      lines,
      timeSeries,
      unit: secondUnit,
    });

    const secondUnitHasStackedLines = isNil(secondUnit)
      ? false
      : hasUnitStackedLines({ lines, unit: secondUnit });

    const stackedValues = secondUnitHasStackedLines
      ? getStackedMetricValues({
          lines: getSortedStackedLines(lines),
          timeSeries,
        })
      : [0];

    return getScale({ height: graphHeight, values, stackedValues });
  }, [timeSeries, lines, secondUnit, graphHeight]);

  const bisectDate = bisector(identity).left;

  const getTimeValue = (x: number): TimeValue => {
    const date = xScale.invert(x - margin.left);
    const index = bisectDate(getDates(timeSeries), date, 1);

    return timeSeries[index];
  };

  const updateMetricsValue = ({ x, y }): void => {
    const timeValue = getTimeValue(x);

    const metrics = getMetrics(timeValue);

    const metricsToDisplay = metrics.filter((metric) => {
      const line = getLineForMetric({ lines, metric });

      return !isNil(timeValue[metric]) && !isNil(line);
    });

    changeMetricsValue({
      newMetricsValue: {
        x,
        y,
        timeValue,
        metrics: metricsToDisplay,
        lines,
        base,
      },
      displayTooltipValues,
    });
  };

  const displayTooltip = React.useCallback(
    (event) => {
      setIsMouseOver(true);
      const { x, y } = localPoint(event) || { x: 0, y: 0 };

      const mouseX = x - margin.left;

      annotations.changeAnnotationHovered({
        mouseX,
        xScale,
        timeline,
      });

      if (zoomPivotPosition) {
        setZoomBoundaries({
          start: lt(mouseX, zoomPivotPosition) ? mouseX : zoomPivotPosition,
          end: gte(mouseX, zoomPivotPosition) ? mouseX : zoomPivotPosition,
        });
        changeMetricsValue({ newMetricsValue: null, displayTooltipValues });
        hideTooltip();
        return;
      }

      updateMetricsValue({ x, y });

      onTooltipDisplay?.([x, y]);
    },
    [containerBounds, lines, zoomBoundaries, timeline, displayTooltipValues],
  );

  React.useEffect(() => {
    const { top, bottom } = context;

    const isWithinBounds =
      containerBounds.top > top && containerBounds.bottom < bottom;

    if (isMouseOver || !isWithinBounds) {
      return;
    }

    if (isNil(tooltipPosition)) {
      changeMetricsValue({ newMetricsValue: null, displayTooltipValues });
      hideTooltip();
      return;
    }

    const [x, y] = tooltipPosition;

    updateMetricsValue({ x, y });
  }, [tooltipPosition]);

  React.useEffect(() => {
    if (not(displayTooltipValues)) {
      hideTooltip();
    }
  }, [displayTooltipValues]);

  const closeZoomPreview = () => {
    setZoomBoundaries(null);
    setZoomPivotPosition(null);
  };

  const closeTooltip = (): void => {
    changeMetricsValue({ newMetricsValue: null, displayTooltipValues });
    hideTooltip();
    setIsMouseOver(false);
    onTooltipDisplay?.();
    annotations.setAnnotationHovered(undefined);

    if (not(isNil(zoomPivotPosition))) {
      return;
    }
    closeZoomPreview();
  };

  const displayAddCommentTooltip = (event): void => {
    setZoomBoundaries(null);
    setZoomPivotPosition(null);
    if (!canComment([resource]) || isNil(onAddComment)) {
      return;
    }

    if (zoomBoundaries?.start !== zoomBoundaries?.end) {
      applyZoom?.({
        start: xScale.invert(zoomBoundaries?.start || 0),
        end: xScale.invert(zoomBoundaries?.end || graphWidth),
      });
      return;
    }

    const { x, y } = localPoint(event) || { x: 0, y: 0 };

    const { timeTick } = getTimeValue(x);
    const date = new Date(timeTick);

    setCommentDate(date);

    const displayLeft = width - x < commentTooltipWidth;

    showAddCommentTooltip({
      tooltipLeft: displayLeft ? x - commentTooltipWidth : x,
      tooltipTop: y,
    });
  };

  const prepareAddComment = (): void => {
    setAddingComment(true);
    hideAddCommentTooltip();
  };

  const confirmAddComment = (comment): void => {
    setAddingComment(false);
    onAddComment?.(comment);
  };

  const displayZoomPreview = (event) => {
    if (isNil(onAddComment)) {
      return;
    }
    const { x } = localPoint(event) || { x: 0 };

    const mouseX = x - margin.left;

    setZoomPivotPosition(mouseX);
    setZoomBoundaries({
      start: mouseX,
      end: mouseX,
    });
    hideAddCommentTooltip();
  };

  const containsMetrics = metricsValue && not(isEmpty(metricsValue?.metrics));

  const tooltipLineX = (metricsValue?.x || tooltipLeft || 0) - margin.left;
  const tooltipLineY = (metricsValue?.y || tooltipTop || 0) - margin.top;

  const zoomBarWidth = Math.abs(
    (zoomBoundaries?.end || 0) - (zoomBoundaries?.start || 0),
  );

  return (
    <AnnotationsContext.Provider value={annotations}>
      <ClickAwayListener onClickAway={hideAddCommentTooltip}>
        <div className={classes.container}>
          {tooltipOpen && tooltipData && (
            <TooltipInPortal
              key={Math.random()}
              top={tooltipTop}
              left={tooltipLeft}
              className={classes.tooltip}
            >
              {tooltipData}
            </TooltipInPortal>
          )}
          {loading && (
            <div className={classes.graphLoader}>
              <CircularProgress />
            </div>
          )}
          <svg
            width="100%"
            height={height}
            ref={containerRef}
            onMouseUp={closeZoomPreview}
          >
            <Group left={margin.left} top={margin.top}>
              <MemoizedGridRows
                scale={rightScale || leftScale}
                width={graphWidth}
                height={graphHeight}
                stroke={grey[100]}
              />
              <MemoizedGridColumns
                scale={xScale}
                width={graphWidth}
                height={graphHeight}
                stroke={grey[100]}
              />
              <MemoizedAxes
                base={base}
                graphHeight={graphHeight}
                graphWidth={graphWidth}
                lines={lines}
                leftScale={leftScale}
                rightScale={rightScale}
                xScale={xScale}
                xAxisTickFormat={xAxisTickFormat}
              />
              <MemoizedLines
                timeSeries={timeSeries}
                lines={lines}
                leftScale={leftScale}
                rightScale={rightScale}
                xScale={xScale}
                graphHeight={graphHeight}
              />
              {displayEventAnnotations && (
                <MemoizedAnnotations
                  xScale={xScale}
                  graphHeight={graphHeight}
                  timeline={timeline as Array<TimelineEvent>}
                />
              )}
              <MemoizedBar
                x={zoomBoundaries?.start || 0}
                y={0}
                width={zoomBarWidth}
                height={graphHeight}
                fill={fade(theme.palette.primary.main, 0.2)}
                stroke={fade(theme.palette.primary.main, 0.5)}
              />
              <MemoizedBar
                x={0}
                y={0}
                width={graphWidth}
                height={graphHeight}
                fill="transparent"
                className={classes.overlay}
                onMouseMove={displayTooltip}
                onMouseLeave={closeTooltip}
                onMouseDown={displayZoomPreview}
                onMouseUp={displayAddCommentTooltip}
              />
              {(containsMetrics || tooltipData) && (
                <>
                  <Line
                    from={{ x: tooltipLineX, y: 0 }}
                    to={{ x: tooltipLineX, y: graphHeight }}
                    stroke={grey[400]}
                    strokeWidth={1}
                    pointerEvents="none"
                  />
                  <Line
                    from={{ x: 0, y: tooltipLineY }}
                    to={{ x: graphWidth, y: tooltipLineY }}
                    stroke={grey[400]}
                    strokeWidth={1}
                    pointerEvents="none"
                  />
                </>
              )}
            </Group>
            <TimeShiftContext.Provider
              value={{
                graphHeight,
                graphWidth,
                canAdjustTimePeriod,
                loading,
                marginTop: margin.top,
                marginLeft: margin.left,
                shiftTime,
              }}
            >
              <TimeShiftZones />
            </TimeShiftContext.Provider>
          </svg>
          {addCommentTooltipOpen && (
            <Paper
              className={classes.addCommentTooltip}
              style={{
                left: addCommentTooltipLeft,
                top: addCommentTooltipTop,
                width: commentTooltipWidth,
              }}
            >
              <Typography variant="caption">
                {format({
                  date: new Date(commentDate as Date),
                  formatString: dateTimeFormat,
                })}
              </Typography>
              <Button
                size="small"
                color="primary"
                className={classes.addCommentButton}
                onClick={prepareAddComment}
              >
                {t(labelAddComment)}
              </Button>
            </Paper>
          )}
          {addingComment && (
            <AddCommentForm
              onSuccess={confirmAddComment}
              date={commentDate as Date}
              resource={resource}
              onClose={(): void => {
                setAddingComment(false);
              }}
            />
          )}
        </div>
      </ClickAwayListener>
    </AnnotationsContext.Provider>
  );
};

const memoProps = [
  'addCommentTooltipLeft',
  'addCommentTooltipTop',
  'addCommentTooltipOpen',
  'width',
  'height',
  'timeSeries',
  'base',
  'lines',
  'xAxisTickFormat',
  'timeline',
  'tooltipPosition',
  'resource',
  'eventAnnotationsActive',
  'loading',
  'canAdjustTimePeriod',
];

const MemoizedGraphContent = memoizeComponent<GraphContentProps>({
  memoProps,
  Component: GraphContent,
});

const Graph = (
  props: Omit<
    GraphContentProps,
    | 'addCommentTooltipLeft'
    | 'addCommentTooltipTop'
    | 'addCommentTooltipOpen'
    | 'showAddCommentTooltip'
    | 'hideAddCommentTooltip'
    | 'format'
  >,
): JSX.Element => {
  const { format } = useLocaleDateTimeFormat();
  const {
    tooltipLeft: addCommentTooltipLeft,
    tooltipTop: addCommentTooltipTop,
    tooltipOpen: addCommentTooltipOpen,
    showTooltip: showAddCommentTooltip,
    hideTooltip: hideAddCommentTooltip,
  } = useTooltip();

  return (
    <MemoizedGraphContent
      {...props}
      addCommentTooltipLeft={addCommentTooltipLeft}
      addCommentTooltipTop={addCommentTooltipTop}
      addCommentTooltipOpen={addCommentTooltipOpen}
      showAddCommentTooltip={showAddCommentTooltip}
      hideAddCommentTooltip={hideAddCommentTooltip}
      format={format}
    />
  );
};

export default Graph;
