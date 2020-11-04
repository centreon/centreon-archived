import * as React from 'react';

import {
  AreaStack,
  Line,
  Bar,
  ParentSize,
  scaleTime,
  scaleLinear,
  GradientOrangeRed,
  browserUsage,
  Group,
  GridRows,
  GridColumns,
  AxisBottom,
  AxisLeft,
  LinePath,
  curveBasis,
  AreaClosed,
  AxisRight,
  useTooltip,
  useTooltipInPortal,
  localPoint,
  TooltipWithBounds,
  defaultStyles,
} from '@visx/visx';

import {
  ComposedChart,
  XAxis,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
} from 'recharts';
import {
  pipe,
  map,
  prop,
  propEq,
  find,
  path,
  reject,
  sortBy,
  isEmpty,
  isNil,
  head,
  last,
  keys,
  equals,
  uniq,
  apply,
} from 'ramda';
import { useTranslation } from 'react-i18next';
import { max, extent, bisector } from 'd3-array';

import { makeStyles, Typography, Theme, fade } from '@material-ui/core';

import {
  useRequest,
  getData,
  useLocaleDateTimeFormat,
  timeFormat,
  dateTimeFormat,
} from '@centreon/ui';

import { grey } from '@material-ui/core/colors';
import getTimeSeries, { getLineData } from './timeSeries';
import { GraphData, TimeValue, Line as LineModel } from './models';
import { labelNoDataForThisPeriod } from '../../translatedLabels';
import LoadingSkeleton from './LoadingSkeleton';
import Legend from './Legend';
import getGraphLines from './Lines';
import formatMetricValue from './formatMetricValue';

const fontFamily = 'Roboto, sans-serif';

interface Props {
  endpoint?: string;
  xAxisTickFormat?: string;
  graphHeight: number;
  toggableLegend?: boolean;
}

const useStyles = makeStyles<Theme, Pick<Props, 'graphHeight'>>((theme) => ({
  container: {
    display: 'grid',
    flexDirection: 'column',
    gridTemplateRows: ({ graphHeight }): string => `auto ${graphHeight}px auto`,
    gridGap: theme.spacing(1),
    height: '100%',
    justifyItems: 'center',
  },
  noDataContainer: {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    height: '100%',
  },
  graph: {
    width: '100%',
    height: '100%',
  },
  legend: {
    display: 'flex',
    flexWrap: 'wrap',
    justifyContent: 'center',
    alignItems: 'center',
    width: '100%',
  },
}));

const margin = { top: 10, right: 30, bottom: 30, left: 40 };

interface Proppies {
  width: number;
  height: number;
  timeSeries: Array<TimeValue>;
  base: number;
  lines: Array<LineModel>;
}

const Graphy = ({
  width,
  height,
  timeSeries,
  base,
  lines,
}: Proppies): JSX.Element => {
  const {
    tooltipData,
    tooltipLeft,
    tooltipTop,
    tooltipOpen,
    showTooltip,
    hideTooltip,
  } = useTooltip();

  const getKeysForSeries = (series) =>
    pipe(keys, reject(equals('timeTick')))(series);

  const { containerRef, containerBounds } = useTooltipInPortal({
    detectBounds: true,
    scroll: true,
  });

  const getUnits = (): Array<string> => {
    return pipe(map(prop('unit')), uniq)(lines);
  };

  const multipleYAxes = getUnits().length < 3;

  const getDateForSeries = (series) => {
    return new Date(series.timeTick).valueOf();
  };

  const getMetricValuesForSeries = (series): Array<number> => {
    const getValue = (key): number => prop(key, series);

    return pipe(getKeysForSeries, map(getValue), reject(isNil))(series);
  };

  const getMin = (values) => {
    return Math.min(...values);
  };

  const getMax = (values) => {
    return Math.max(...values);
  };

  const yMax = height - margin.top - margin.bottom;
  const xMax = width - margin.left - margin.right;

  const xScale = scaleTime<number>({
    range: [0, xMax],
    domain: [
      getMin(timeSeries.map(getDateForSeries)),
      getMax(timeSeries.map(getDateForSeries)),
    ],
  });
  const yScale = scaleLinear<number>({
    domain: [
      getMin(
        timeSeries.map((series) => getMin(getMetricValuesForSeries(series))),
      ),
      getMax(
        timeSeries.map((series) => getMax(getMetricValuesForSeries(series))),
      ),
    ],
    nice: true,
    range: [yMax, 0],
  });

  const formatTick = ({ unit }) => (value): string => {
    if (isNil(value)) {
      return '';
    }

    return formatMetricValue({ value, unit, base }) as string;
  };

  const bisectDate = bisector((d) => {
    return d;
  }).left;

  const getTooltipData = (index: number): JSX.Element => {
    const series = timeSeries[index];

    const metrics = pipe(keys, reject(equals('timeTick')))(series);

    const nonEmptyMetrics = metrics.filter((metric) => {
      return !isNil(series[metric]);
    });

    return (
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
        }}
      >
        {nonEmptyMetrics.map((metric) => {
          const value = series[metric];

          const line = find(propEq('metric', metric), lines);

          const color = line?.color;
          const name = line?.name;
          const unit = line?.unit;

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

  const handleMouseOver = React.useCallback(
    (event) => {
      const { x, y } = localPoint(event) || { x: 0, y: 0 };

      const xDomain = xScale.invert(x - margin.left);

      const times = timeSeries
        .map(prop('timeTick'))
        .map((tick) => new Date(tick));

      const index = bisectDate(times, xDomain, 1);

      showTooltip({
        tooltipLeft: x,
        tooltipTop: y,
        tooltipData: getTooltipData(index),
      });
    },
    [showTooltip, containerBounds],
  );
  const tooltipStyles = {
    ...defaultStyles,
    opacity: 0.8,
    padding: 12,
  };

  return (
    <div
      style={{
        position: 'relative',
      }}
    >
      {tooltipOpen && (
        <TooltipWithBounds
          key={Math.random()}
          top={tooltipTop}
          left={tooltipLeft}
          style={tooltipStyles}
        >
          {tooltipData}
        </TooltipWithBounds>
      )}
      <svg
        ref={containerRef}
        width={width}
        height={height}
        onMouseMove={handleMouseOver}
        onMouseLeave={hideTooltip}
      >
        <Group left={margin.left} top={margin.top}>
          <AxisBottom top={yMax} scale={xScale} />
          <AxisLeft scale={yScale} tickFormat={formatTick({ unit: '' })} />
          <GridRows
            scale={yScale}
            width={xMax}
            height={yMax}
            stroke={grey[100]}
          />
          <GridColumns
            scale={xScale}
            width={xMax}
            height={yMax}
            stroke={grey[100]}
          />

          {lines.map(
            ({
              metric,
              areaColor,
              transparency,
              lineColor,
              filled,
              unit,
              highlight,
            }) => {
              const getOpacity = (): number => {
                if (highlight === false) {
                  return 0.3;
                }

                return 1;
              };

              const props = {
                data: timeSeries,
                unit,
                stroke: lineColor,
                strokeWidth: highlight ? 2 : 1,
                opacity: getOpacity(),
                y: (series) => yScale(prop(metric, series)),
                x: (series) => xScale(getDateForSeries(series)),
                curve: curveBasis,
                yScale,
              };

              if (filled) {
                return (
                  <AreaClosed
                    key={metric}
                    fill={
                      transparency
                        ? fade(areaColor, 1 - transparency * 0.01)
                        : undefined
                    }
                    {...props}
                  />
                );
              }

              return <LinePath key={metric} {...props} />;
            },
          )}
          <Bar
            x={margin.left}
            y={margin.top}
            width={innerWidth}
            height={innerHeight}
            fill="transparent"
            rx={14}
            // onTouchStart={handleTooltip}
            // onTouchMove={handleTooltip}
            // onMouseMove={handleTooltip}
            onMouseLeave={() => hideTooltip()}
          />
          <Line
            from={{ x: tooltipLeft - margin.left, y: 0 }}
            to={{ x: tooltipLeft - margin.left, y: 10 }}
            stroke={grey[300]}
            strokeWidth={2}
            pointerEvents="none"
            strokeDasharray="5,2"
          />
        </Group>
      </svg>
    </div>
  );
};

const PerformanceGraph = ({
  endpoint,
  graphHeight,
  xAxisTickFormat = timeFormat,
  toggableLegend = false,
}: Props): JSX.Element | null => {
  const classes = useStyles({ graphHeight });
  const { t } = useTranslation();
  const { format } = useLocaleDateTimeFormat();

  const [timeSeries, setTimeSeries] = React.useState<Array<TimeValue>>([]);
  const [lineData, setLineData] = React.useState<Array<LineModel>>([]);
  const [title, setTitle] = React.useState<string>();
  const [base, setBase] = React.useState<number>();

  const { sendRequest, sending } = useRequest<GraphData>({
    request: getData,
  });

  // const dateScale = React.useMemo(
  //   () =>
  //     scaleTime({
  //       range: [0, 100],
  //       domain: extent(timeSeries, getDate) as [Date, Date],
  //     }),
  //   [timeSeries],
  // );

  React.useEffect(() => {
    if (isNil(endpoint)) {
      return;
    }

    sendRequest(endpoint).then((graphData) => {
      setTimeSeries(getTimeSeries(graphData));
      setLineData(getLineData(graphData));
      setTitle(graphData.global.title);
      setBase(graphData.global.base);
    });
  }, [endpoint]);

  if (sending || isNil(endpoint)) {
    return <LoadingSkeleton />;
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

  const formatTooltipValue = (
    value,
    metric,
    { unit },
  ): Array<string | null> => {
    const legendName = pipe(
      find(propEq('metric', metric)),
      path(['name']),
    )(lineData) as string;

    return [formatMetricValue({ value, unit, base }), legendName];
  };

  const formatXAxisTick = (tick): string =>
    format({ date: new Date(tick), formatString: xAxisTickFormat });

  const formatTooltipTime = (tick): string =>
    format({ date: new Date(tick), formatString: dateTimeFormat });

  const getLineByMetric = (metric): LineModel => {
    return find(propEq('metric', metric), lineData) as LineModel;
  };

  const toggleMetricDisplay = (metric): void => {
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

  return (
    <div className={classes.container}>
      <Typography variant="body1" color="textPrimary">
        {title}
      </Typography>
      {/* <AreaClosed data={timeSeries} x={d => dateScale(getDate(d))} /> */}

      <ParentSize>
        {({ width, height }) => (
          <Graphy
            width={width}
            height={height}
            timeSeries={timeSeries}
            lines={displayedLines}
            base={base}
          />
        )}
      </ParentSize>
      {/* <ResponsiveContainer className={classes.graph}>
        <ComposedChart data={timeSeries} stackOffset="sign">
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis
            dataKey="timeTick"
            tickFormatter={formatXAxisTick}
            tick={{ fontSize: 13 }}
          />

          {getGraphLines({ lines: displayedLines, base })}

          <Tooltip
            labelFormatter={formatTooltipTime}
            formatter={formatTooltipValue}
            contentStyle={{ fontFamily }}
            wrapperStyle={{ opacity: 0.7 }}
            isAnimationActive={false}
            filterNull
          />
        </ComposedChart>
      </ResponsiveContainer> */}
      <div className={classes.legend}>
        {/* <Legend
          lines={sortedLines}
          onItemToggle={toggleMetricDisplay}
          toggable={toggableLegend}
          onItemHighlight={highlightLine}
          onClearItemHighlight={clearHighlight}
        /> */}
      </div>
    </div>
  );
};

export default PerformanceGraph;
export { fontFamily };
