import * as React from 'react';

import { equals, isNil, isEmpty, identity, min, max, not } from 'ramda';
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

import { Typography } from '@material-ui/core';
import { grey } from '@material-ui/core/colors';

import { useLocaleDateTimeFormat, dateTimeFormat } from '@centreon/ui';

import { TimeValue, Line as LineModel } from '../models';
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
import formatMetricValue from '../formatMetricValue';
import Lines from '../Lines';

import Axes from './Axes';

const propsAreEqual = (prevProps, nextProps): boolean =>
  equals(prevProps, nextProps);

const MemoizedAxes = React.memo(Axes, propsAreEqual);
const MemoizedBar = React.memo(Bar, propsAreEqual);
const MemoizedGridColumns = React.memo(GridColumns, propsAreEqual);
const MemoizedGridRows = React.memo(GridRows, propsAreEqual);
const MemoizedLines = React.memo(Lines, propsAreEqual);

const margin = { top: 10, right: 45, bottom: 30, left: 45 };

interface Props {
  width: number;
  height: number;
  timeSeries: Array<TimeValue>;
  base: number;
  lines: Array<LineModel>;
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

const Graph = ({
  width,
  height,
  timeSeries,
  base,
  lines,
  xAxisTickFormat,
}: Props): JSX.Element => {
  const { format } = useLocaleDateTimeFormat();

  const {
    tooltipData,
    tooltipLeft,
    tooltipTop,
    tooltipOpen,
    showTooltip,
    hideTooltip,
  } = useTooltip();

  const { containerRef, containerBounds } = useTooltipInPortal({
    detectBounds: true,
    scroll: true,
  });

  const graphWidth = width > 0 ? width - margin.left - margin.right : 0;
  const graphHeight = height > 0 ? height - margin.top - margin.bottom : 0;

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

  const getTooltipData = (index: number): JSX.Element | undefined => {
    const timeValue = timeSeries[index] as TimeValue;

    const metrics = getMetrics(timeValue);

    const metricsToDisplay = metrics.filter((metric) => {
      const line = getLineForMetric({ lines, metric });

      return !isNil(timeValue[metric]) && !isNil(line);
    });

    if (isEmpty(metricsToDisplay)) {
      return undefined;
    }

    return (
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
        }}
      >
        <Typography variant="caption">
          {format({
            date: new Date(timeValue.timeTick),
            formatString: dateTimeFormat,
          })}
        </Typography>
        {metricsToDisplay.map((metric) => {
          const value = timeValue[metric] as number;

          const { color, name, unit } = getLineForMetric({
            lines,
            metric,
          }) as LineModel;

          const formattedValue = formatMetricValue({ value, unit, base });

          return (
            <Typography
              key={metric}
              variant="caption"
              style={{
                color,
              }}
            >
              {`${name} ${formattedValue}`}
            </Typography>
          );
        })}
      </div>
    );
  };

  const displayTooltip = React.useCallback(
    (event) => {
      const { x, y } = localPoint(event) || { x: 0, y: 0 };

      const xDomain = xScale.invert(x - margin.left);

      const index = bisectDate(getDates(timeSeries), xDomain, 1);

      showTooltip({
        tooltipLeft: x,
        tooltipTop: y,
        tooltipData: getTooltipData(index),
      });
    },
    [showTooltip, containerBounds, lines],
  );

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
    </div>
  );
};

export default Graph;
