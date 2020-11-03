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
} from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles, Typography, Theme, fade } from '@material-ui/core';

import {
  useRequest,
  getData,
  useLocaleDateTimeFormat,
  timeFormat,
  dateTimeFormat,
} from '@centreon/ui';

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

interface Props {
  width: number;
  height: number;
  timeSeries: Array<TimeValue>;
  base: number;
  lines: Array<LineModel>;
}

const Graphy = ({ width, height, timeSeries, lines }) => {
  const data = timeSeries;

  const {
    tooltipData,
    tooltipLeft,
    tooltipTop,
    tooltipOpen,
    showTooltip,
    hideTooltip,
  } = useTooltip();

  const timeSeriesKeys = keys<string>(timeSeries).filter(equals('timeTick'));

  // const getTimeSeriesValue = (key: string): number => {
  //   return timeSeries
  // }

  const { containerRef, TooltipInPortal, containerBounds } = useTooltipInPortal(
    {
      // use TooltipWithBounds
      detectBounds: true,
      // when tooltip containers are scrolled, this will correctly update the Tooltip position
      scroll: true,
    },
  );

  const getUnits = (): Array<string> => {
    return pipe(map(prop('unit')), uniq)(lines);
  };

  const multipleYAxes = getUnits().length < 3;

  const date = (d) => new Date(d.timeTick).valueOf();

  const pl = (d) => d.pl;
  const rta = (d) => d.rta;
  const rtmax = (d) => d.rtmax;
  const rtmin = (d) => d.rtmin;

  const yMax = height - margin.top - margin.bottom;
  const xMax = width - margin.left - margin.right;

  // scales
  const xScale = scaleTime<number>({
    range: [0, xMax],
    domain: [Math.min(...data.map(date)), Math.max(...data.map(date))],
  });
  const yScale = scaleLinear<number>({
    domain: [
      Math.min(
        ...timeSeries.map((d) =>
          Math.min(...[pl(d), rta(d), rtmax(d), rtmin(d)]),
        ),
      ),
      Math.max(
        ...timeSeries.map((d) =>
          Math.max(...[pl(d), rta(d), rtmax(d), rtmin(d)]),
        ),
      ),
    ],
    nice: true,
    range: [yMax, 0],
  });

  const formatTick = ({ unit, base }) => (value): string => {
    if (isNil(value)) {
      return '';
    }

    return formatMetricValue({ value, unit, base }) as string;
  };

  const background = '#f3f3f3';

  const handleMouseOver = React.useCallback(
    (event) => {
      const { x, y } = localPoint(event) || { x: 0, y: 0 };

      showTooltip({
        tooltipLeft: x,
        tooltipTop: y,
        tooltipData: 'Plop',
      });
    },
    [showTooltip, containerBounds],
  );
  const tooltipStyles = {
    ...defaultStyles,
    backgroundColor: 'rgba(53,71,125,0.8)',
    color: 'white',
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
          <AxisBottom top={yMax} scale={xScale} tickComponent={Typography} />
          <AxisLeft
            scale={yScale}
            tickFormat={formatTick({ unit: '', base: 1000 })}
          />
          <GridRows
            scale={yScale}
            width={xMax}
            height={yMax}
            stroke="#e0e0e0"
          />
          <GridColumns
            scale={xScale}
            width={xMax}
            height={yMax}
            stroke="#e0e0e0"
          />

          {lineData.map(
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
                y: (series) => yScale(prop(metric, series) ?? 0),
                x: (series) => xScale(date(series) ?? 0),
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
