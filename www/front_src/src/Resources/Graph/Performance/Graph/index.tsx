import * as React from 'react';

import { equals, isNil, identity, isEmpty } from 'ramda';
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
  TooltipWithBounds,
  defaultStyles,
} from '@visx/visx';
import { bisector } from 'd3-array';
import { ScaleLinear } from 'd3-scale';
import { useTranslation } from 'react-i18next';

import { Button } from '@material-ui/core';
import { grey } from '@material-ui/core/colors';

import { TimeValue, Line as LineModel } from '../models';
import {
  getTime,
  getMin,
  getMax,
  getDates,
  getUnits,
  getMetricValuesForUnit,
  getMetricValuesForLines,
  getMetrics,
  getLineForMetric,
} from '../timeSeries';
import Axes from './Axes';
import Lines from '../Lines';
import Annotations from './Annotations';
import { TimelineEvent } from '../../../Details/tabs/Timeline/models';
import MetricsTooltip from './MetricsTooltip';
import { labelAddComment } from '../../../translatedLabels';
import DialogAddComment from './DialogAddComment';

const propsAreEqual = (prevProps, nextProps): boolean =>
  equals(prevProps, nextProps);

const MemoizedAxes = React.memo(Axes, propsAreEqual);
const MemoizedBar = React.memo(Bar, propsAreEqual);
const MemoizedGridColumns = React.memo(GridColumns, propsAreEqual);
const MemoizedGridRows = React.memo(GridRows, propsAreEqual);
const MemoizedLines = React.memo(Lines, propsAreEqual);
const MemoizedAnnotations = React.memo(Annotations, propsAreEqual);

const margin = { top: 30, right: 45, bottom: 30, left: 45 };

interface Props {
  width: number;
  height: number;
  timeSeries: Array<TimeValue>;
  base: number;
  lines: Array<LineModel>;
  xAxisTickFormat: string;
  timeline?: Array<TimelineEvent>;
}

const getScale = ({ values, height }): ScaleLinear<number, number> => {
  const min = getMin(values);
  const max = getMax(values);

  const upperRangeValue = min === max && max === 0 ? height : 0;

  return scaleLinear<number>({
    domain: [getMin(values), getMax(values)],
    nice: true,
    range: [height, upperRangeValue],
  });
};

const Graph = ({
  width,
  height,
  timeSeries,
  base,
  lines,
  xAxisTickFormat,
  timeline,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const [addingComment, setAddingComment] = React.useState(false);

  const {
    tooltipData,
    tooltipLeft,
    tooltipTop,
    tooltipOpen,
    showTooltip,
    hideTooltip,
  } = useTooltip();
  const {
    tooltipLeft: addCommentTooltipLeft,
    tooltipTop: addCommentTooltipTop,
    tooltipOpen: addCommentTooltipOpen,
    showTooltip: showAddCommentTooltip,
    hideTooltip: hideAddCommentTooltip,
  } = useTooltip();

  const { containerRef, containerBounds } = useTooltipInPortal({
    detectBounds: true,
    scroll: true,
  });

  const graphWidth = width > 0 ? width - margin.left - margin.right : 0;
  const graphHeight = height > 0 ? height - margin.top - margin.bottom : 0;

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

    return getScale({ height: graphHeight, values });
  }, [timeSeries, lines, firstUnit, graphHeight]);

  const rightScale = React.useMemo(() => {
    const values = getMetricValuesForUnit({
      lines,
      timeSeries,
      unit: secondUnit,
    });

    return getScale({ height: graphHeight, values });
  }, [timeSeries, lines, secondUnit, graphHeight]);

  const displayTooltip = React.useCallback(
    (event) => {
      const { x, y } = localPoint(event) || { x: 0, y: 0 };

      const xDomain = xScale.invert(x - margin.left);
      const bisectDate = bisector(identity).left;
      const index = bisectDate(getDates(timeSeries), xDomain, 1);
      const timeValue = timeSeries[index];

      const metrics = getMetrics(timeValue);

      const metricsToDisplay = metrics.filter((metric) => {
        const line = getLineForMetric({ lines, metric });

        return !isNil(timeValue[metric]) && !isNil(line);
      });

      showTooltip({
        tooltipLeft: x,
        tooltipTop: y,
        tooltipData: isEmpty(metricsToDisplay) ? undefined : (
          <MetricsTooltip
            timeValue={timeValue}
            lines={lines}
            base={base}
            metrics={metricsToDisplay}
          />
        ),
      });
    },
    [showTooltip, containerBounds, lines],
  );

  const displayAddCommentTooltip = (event): void => {
    const { x, y } = localPoint(event) || { x: 0, y: 0 };

    showAddCommentTooltip({
      tooltipLeft: x,
      tooltipTop: y,
    });
  };

  const prepareAddComment = (): void => {
    setAddingComment(true);
    hideAddCommentTooltip();
  };

  const confirmAddComment = (): void => {
    setAddingComment(false);
    console.log('add comment');
  };

  const tooltipLineLeft = (tooltipLeft as number) - margin.left;

  return (
    <div
      style={{
        position: 'relative',
      }}
    >
      {tooltipOpen && tooltipData && (
        <TooltipWithBounds
          key={Math.random()}
          top={tooltipTop}
          left={tooltipLeft}
          style={{ ...defaultStyles, opacity: 0.8, padding: 12 }}
        >
          {tooltipData}
        </TooltipWithBounds>
      )}
      <svg width={width} height={height} ref={containerRef}>
        <Group left={margin.left} top={margin.top}>
          <MemoizedGridRows
            scale={leftScale}
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
            timeSeries={timeSeries}
          />
          <MemoizedAnnotations
            xScale={xScale}
            graphHeight={graphHeight}
            timeline={timeline as Array<TimelineEvent>}
          />
          <MemoizedLines
            timeSeries={timeSeries}
            lines={lines}
            leftScale={leftScale}
            rightScale={rightScale}
            xScale={xScale}
            graphHeight={graphHeight}
          />
          <MemoizedBar
            x={0}
            y={0}
            width={graphWidth}
            height={graphHeight}
            fill="transparent"
            onClick={displayAddCommentTooltip}
            onMouseMove={displayTooltip}
            onMouseLeave={hideTooltip}
          />
          {tooltipData && (
            <Line
              from={{ x: tooltipLineLeft, y: 0 }}
              to={{ x: tooltipLineLeft, y: graphHeight }}
              stroke={grey[300]}
              strokeWidth={2}
              pointerEvents="none"
            />
          )}
        </Group>
      </svg>
      {addCommentTooltipOpen && (
        <Button
          size="small"
          color="primary"
          style={{
            position: 'absolute',
            left: addCommentTooltipLeft,
            top: addCommentTooltipTop,
            backgroundColor: 'white',
            fontSize: 10,
          }}
          onClick={prepareAddComment}
        >
          {t(labelAddComment)}
        </Button>
      )}
      {addingComment && (
        <DialogAddComment
          onAddComment={confirmAddComment}
          onClose={() => {
            setAddingComment(false);
          }}
        />
      )}
    </div>
  );
};

export default Graph;
