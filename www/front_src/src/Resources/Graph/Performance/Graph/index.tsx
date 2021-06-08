import * as React from 'react';

import { equals, isNil, identity, min, max, not, lt, gte } from 'ramda';
import {
  Line,
  Bar,
  scaleTime,
  scaleLinear,
  Group,
  GridRows,
  GridColumns,
  useTooltip,
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

import { TimeValue, Line as LineModel, AdjustTimePeriodProps } from '../models';
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
import memoizeComponent from '../../../memoizedComponent';
import { useMousePositionContext } from '../ExportableGraphWithTimeline/useMousePosition';

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

const margin = { bottom: 30, left: 45, right: 45, top: 30 };

const commentTooltipWidth = 165;

interface Props {
  base: number;
  height: number;
  lines: Array<LineModel>;
  onAddComment?: (commentParameters: CommentParameters) => void;
  resource: Resource | ResourceDetails;
  timeSeries: Array<TimeValue>;
  timeline?: Array<TimelineEvent>;
  width: number;
  xAxisTickFormat: string;
}

const useStyles = makeStyles<Theme, Pick<Props, 'onAddComment'>>((theme) => ({
  addCommentButton: {
    fontSize: 10,
  },
  addCommentTooltip: {
    display: 'grid',
    fontSize: 10,
    gridAutoFlow: 'row',
    justifyItems: 'center',
    padding: theme.spacing(0.5),
    position: 'absolute',
  },
  container: {
    position: 'relative',
  },
  graphLoader: {
    alignItems: 'center',
    backgroundColor: fade(theme.palette.common.white, 0.5),
    display: 'flex',
    height: '100%',
    justifyContent: 'center',
    position: 'absolute',
    width: '100%',
  },
  overlay: {
    cursor: ({ onAddComment }): string =>
      isNil(onAddComment) ? 'normal' : 'crosshair',
  },
  tooltip: {
    padding: 12,
    zIndex: theme.zIndex.tooltip,
  },
}));

interface ZoomBoundaries {
  end: number;
  start: number;
}

interface GraphContentProps {
  addCommentTooltipLeft?: number;
  addCommentTooltipOpen: boolean;
  addCommentTooltipTop?: number;
  applyZoom?: (props: AdjustTimePeriodProps) => void;
  base: number;
  canAdjustTimePeriod: boolean;
  changeMetricsValue: (props) => void;
  containsMetrics: boolean;
  displayEventAnnotations: boolean;
  format: (parameters) => string;
  height: number;
  hideAddCommentTooltip: () => void;
  lines: Array<LineModel>;
  loading: boolean;
  onAddComment?: (commentParameters: CommentParameters) => void;
  resource: Resource | ResourceDetails;
  shiftTime?: (direction: TimeShiftDirection) => void;
  showAddCommentTooltip: (args) => void;
  timeSeries: Array<TimeValue>;
  timeline?: Array<TimelineEvent>;
  width: number;
  xAxisTickFormat: string;
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

export const bisectDate = bisector(identity).center;

const GraphContent = ({
  width,
  height,
  timeSeries,
  base,
  lines,
  xAxisTickFormat,
  timeline,
  resource,
  addCommentTooltipLeft,
  addCommentTooltipTop,
  addCommentTooltipOpen,
  onAddComment,
  hideAddCommentTooltip,
  showAddCommentTooltip,
  format,
  applyZoom,
  shiftTime,
  loading,
  canAdjustTimePeriod,
  displayEventAnnotations,
  containsMetrics,
  changeMetricsValue,
}: GraphContentProps): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles({ onAddComment });

  const [addingComment, setAddingComment] = React.useState(false);
  const [commentDate, setCommentDate] = React.useState<Date>();
  const [zoomPivotPosition, setZoomPivotPosition] = React.useState<
    number | null
  >(null);
  const [zoomBoundaries, setZoomBoundaries] =
    React.useState<ZoomBoundaries | null>(null);
  const { canComment } = useAclQuery();

  const theme = useTheme();
  const [isMouseOver, setIsMouseOver] = React.useState(false);

  const graphWidth = width > 0 ? width - margin.left - margin.right : 0;
  const graphHeight = height > 0 ? height - margin.top - margin.bottom : 0;

  const annotations = useAnnotations(graphWidth);

  const { mousePosition, setMousePosition } = useMousePositionContext();

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
        domain: [
          getMin(timeSeries.map(getTime)),
          getMax(timeSeries.map(getTime)),
        ],
        range: [0, graphWidth],
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

    return getScale({ height: graphHeight, stackedValues, values });
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

    return getScale({ height: graphHeight, stackedValues, values });
  }, [timeSeries, lines, secondUnit, graphHeight]);

  const getTimeValue = (x: number): TimeValue => {
    const date = xScale.invert(x - margin.left);
    const index = bisectDate(getDates(timeSeries), date);

    return timeSeries[index];
  };

  const updateMetricsValue = ({ x }): void => {
    const timeValue = getTimeValue(x);

    const metrics = getMetrics(timeValue);

    const metricsToDisplay = metrics.filter((metric) => {
      const line = getLineForMetric({ lines, metric });

      return !isNil(timeValue[metric]) && !isNil(line);
    });

    changeMetricsValue({
      newMetricsValue: {
        base,
        lines,
        metrics: metricsToDisplay,
        timeValue,
      },
    });
  };

  const displayTooltip = (event) => {
    setIsMouseOver(true);
    const { x, y } = localPoint(event) || { x: 0, y: 0 };

    const mouseX = x - margin.left;

    annotations.changeAnnotationHovered({
      mouseX,
      timeline,
      xScale,
    });

    if (zoomPivotPosition) {
      setZoomBoundaries({
        end: gte(mouseX, zoomPivotPosition) ? mouseX : zoomPivotPosition,
        start: lt(mouseX, zoomPivotPosition) ? mouseX : zoomPivotPosition,
      });
      changeMetricsValue({ newMetricsValue: null });
      return;
    }

    updateMetricsValue({ x });
    setMousePosition([x, y]);
  };

  React.useEffect(() => {
    if (isMouseOver) {
      return;
    }

    if (isNil(mousePosition)) {
      changeMetricsValue({ newMetricsValue: null });
      return;
    }

    const [x] = mousePosition;

    updateMetricsValue({ x });
  }, [mousePosition]);

  const closeZoomPreview = () => {
    setZoomBoundaries(null);
    setZoomPivotPosition(null);
  };

  const closeTooltip = (): void => {
    changeMetricsValue({ newMetricsValue: null });
    setIsMouseOver(false);
    setMousePosition(null);
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
        end: xScale.invert(zoomBoundaries?.end || graphWidth),
        start: xScale.invert(zoomBoundaries?.start || 0),
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
      end: mouseX,
      start: mouseX,
    });
    hideAddCommentTooltip();
  };

  const mousePositionX = (mousePosition?.[0] || 0) - margin.left;
  const mousePositionY = (mousePosition?.[1] || 0) - margin.top;

  const zoomBarWidth = Math.abs(
    (zoomBoundaries?.end || 0) - (zoomBoundaries?.start || 0),
  );

  const mousePositionTimeTick = mousePosition
    ? getTimeValue(mousePosition[0]).timeTick
    : 0;

  const timeTick = containsMetrics ? new Date(mousePositionTimeTick) : null;

  return (
    <AnnotationsContext.Provider value={annotations}>
      <ClickAwayListener onClickAway={hideAddCommentTooltip}>
        <div className={classes.container}>
          {loading && (
            <div className={classes.graphLoader}>
              <CircularProgress />
            </div>
          )}
          <svg height={height} width="100%" onMouseUp={closeZoomPreview}>
            <Group left={margin.left} top={margin.top}>
              <MemoizedGridRows
                height={graphHeight}
                scale={rightScale || leftScale}
                stroke={grey[100]}
                width={graphWidth}
              />
              <MemoizedGridColumns
                height={graphHeight}
                scale={xScale}
                stroke={grey[100]}
                width={graphWidth}
              />
              <MemoizedAxes
                base={base}
                graphHeight={graphHeight}
                graphWidth={graphWidth}
                leftScale={leftScale}
                lines={lines}
                rightScale={rightScale}
                xAxisTickFormat={xAxisTickFormat}
                xScale={xScale}
              />
              <MemoizedLines
                graphHeight={graphHeight}
                leftScale={leftScale}
                lines={lines}
                rightScale={rightScale}
                timeSeries={timeSeries}
                timeTick={timeTick}
                xScale={xScale}
              />
              {displayEventAnnotations && (
                <MemoizedAnnotations
                  graphHeight={graphHeight}
                  timeline={timeline as Array<TimelineEvent>}
                  xScale={xScale}
                />
              )}
              <MemoizedBar
                fill={fade(theme.palette.primary.main, 0.2)}
                height={graphHeight}
                stroke={fade(theme.palette.primary.main, 0.5)}
                width={zoomBarWidth}
                x={zoomBoundaries?.start || 0}
                y={0}
              />
              {containsMetrics && (
                <>
                  <Line
                    from={{ x: mousePositionX, y: 0 }}
                    pointerEvents="none"
                    stroke={grey[400]}
                    strokeWidth={1}
                    to={{ x: mousePositionX, y: graphHeight }}
                  />
                  <Line
                    from={{ x: 0, y: mousePositionY }}
                    pointerEvents="none"
                    stroke={grey[400]}
                    strokeWidth={1}
                    to={{ x: graphWidth, y: mousePositionY }}
                  />
                </>
              )}
              <MemoizedBar
                className={classes.overlay}
                fill="transparent"
                height={graphHeight}
                width={graphWidth}
                x={0}
                y={0}
                onMouseDown={displayZoomPreview}
                onMouseLeave={closeTooltip}
                onMouseMove={displayTooltip}
                onMouseUp={displayAddCommentTooltip}
              />
            </Group>
            <TimeShiftContext.Provider
              value={{
                canAdjustTimePeriod,
                graphHeight,
                graphWidth,
                loading,
                marginLeft: margin.left,
                marginTop: margin.top,
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
                className={classes.addCommentButton}
                color="primary"
                size="small"
                onClick={prepareAddComment}
              >
                {t(labelAddComment)}
              </Button>
            </Paper>
          )}
          {addingComment && (
            <AddCommentForm
              date={commentDate as Date}
              resource={resource}
              onClose={(): void => {
                setAddingComment(false);
              }}
              onSuccess={confirmAddComment}
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
  'resource',
  'loading',
  'canAdjustTimePeriod',
  'displayTooltipValues',
  'displayEventAnnotations',
  'containsMetrics',
  'isInViewport',
];

const MemoizedGraphContent = memoizeComponent<GraphContentProps>({
  Component: GraphContent,
  memoProps,
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
    | 'changeMetricsValue'
    | 'isInViewport'
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
  const { changeMetricsValue } = useMetricsValueContext();

  return (
    <MemoizedGraphContent
      {...props}
      addCommentTooltipLeft={addCommentTooltipLeft}
      addCommentTooltipOpen={addCommentTooltipOpen}
      addCommentTooltipTop={addCommentTooltipTop}
      changeMetricsValue={changeMetricsValue}
      format={format}
      hideAddCommentTooltip={hideAddCommentTooltip}
      showAddCommentTooltip={showAddCommentTooltip}
    />
  );
};

export default Graph;
