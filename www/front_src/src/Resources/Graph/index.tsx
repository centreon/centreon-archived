import * as React from 'react';

import {
  ComposedChart,
  Area,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Legend,
  ResponsiveContainer,
  Tooltip,
} from 'recharts';
import filesize from 'filesize';
import format from 'date-fns/format';

import { fade, makeStyles, Typography, Grid } from '@material-ui/core';

import { Skeleton } from '@material-ui/lab';
import useGet from '../useGet';

const JSXXAxis = (XAxis as unknown) as (props) => JSX.Element;
const JSXYAxis = (YAxis as unknown) as (props) => JSX.Element;

const useStyles = makeStyles((theme) => ({
  container: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: theme.palette.common.white,
    height: '100%',
    width: '100%',
  },
  loadingSkeleton: {
    flexGrow: 1,
    paddingLeft: theme.spacing(2),
    paddingRight: theme.spacing(2),
    height: '100%',
  },
  loadingSkeletonLine: {
    transform: 'none',
  },
  graph: {
    margin: 'auto',
  },
  legend: {
    color: theme.palette.common.black,
  },
}));

interface Props {
  endpoint: string;
}

interface Metric {
  data: Array<number>;
  ds_data;
  metric: string;
  unit: string;
}

interface GraphData {
  global;
  metrics: Array<Metric>;
  times: Array<string>;
}

interface MetricData {
  [metric: string]: string;
}

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <Grid
      container
      direction="column"
      spacing={2}
      className={classes.loadingSkeleton}
    >
      <Grid item style={{ flexGrow: 1 }}>
        <Skeleton height="100%" className={classes.loadingSkeletonLine} />
      </Grid>
      <Grid item>
        <Skeleton className={classes.loadingSkeletonLine} />
      </Grid>
    </Grid>
  );
};

const Graph = ({ endpoint }: Props): JSX.Element => {
  const [graphData, setGraphData] = React.useState<GraphData>();

  const get = useGet({
    endpoint,
    onSuccess: setGraphData,
  });

  const classes = useStyles();

  React.useEffect(() => {
    get();
  }, []);

  const getMetricDataForTimeIndex = (timeIndex): MetricData | undefined => {
    return graphData?.metrics.reduce((metricByName, { metric, data }) => {
      const dataForTimeIndex = data[timeIndex];
      const lowerLimit =
        graphData.global['lower-limit'] || dataForTimeIndex - 1;

      return {
        ...metricByName,
        ...(dataForTimeIndex > lowerLimit
          ? { [metric]: dataForTimeIndex }
          : null),
      };
    }, {});
  };

  const getBase = (unit): 2 | 10 => {
    const base2Units = [
      'B',
      'bytes',
      'bytespersecond',
      'B/s',
      'B/sec',
      'o',
      'octets',
    ];

    return base2Units.includes(unit) ? 2 : 10;
  };

  const data =
    graphData?.times.map((time, timeIndex) => {
      return { time, ...getMetricDataForTimeIndex(timeIndex) };
    }) ?? [];

  const getUnits = (): Array<string> => [
    ...new Set(graphData?.metrics?.map(({ unit }) => unit)),
  ];

  const yAxisFormatter = ({ tick, unit }): string =>
    unit === ''
      ? tick
      : filesize(tick, { base: getBase(unit) }).replace('B', '');

  const xAxisFormatter = (tick): string =>
    format(new Date(Number(tick) * 1000), 'HH:mm');

  const YAxes =
    getUnits().length < 3 ? (
      getUnits().map((unit, index) => (
        <JSXYAxis
          yAxisId={unit}
          key={unit}
          unit={unit}
          orientation={index === 0 ? 'left' : 'right'}
          tickFormatter={(tick): string => yAxisFormatter({ tick, unit })}
        />
      ))
    ) : (
      <JSXYAxis />
    );

  const legendFormatter = (value): JSX.Element => (
    <Typography variant="caption" color="textPrimary">
      {value}
    </Typography>
  );

  const loading = graphData === undefined;
  const hasData = graphData && graphData?.times.length > 0;
  // const hasData = false;

  return (
    <div className={classes.container}>
      {loading && <LoadingSkeleton />}
      {hasData && (
        <ResponsiveContainer>
          <ComposedChart className={classes.graph} data={data}>
            <CartesianGrid strokeDasharray="3 3" />
            <JSXXAxis dataKey="time" tickFormatter={xAxisFormatter} />
            {YAxes}
            {graphData?.metrics.map(({ metric, ds_data, unit }, index) =>
              ds_data.ds_filled ? (
                <Area
                  key={metric}
                  type="monotone"
                  dataKey={metric}
                  stackId={index}
                  stroke={ds_data.ds_color_line}
                  fill={fade(
                    ds_data.ds_color_area,
                    ds_data.ds_transparency * 0.01,
                  )}
                  yAxisId={unit}
                />
              ) : (
                <Line
                  key={metric}
                  type="monotone"
                  dataKey={metric}
                  stroke={ds_data.ds_color_line}
                  dot={false}
                  yAxisId={unit}
                />
              ),
            )}
            <Legend
              formatter={legendFormatter}
              iconType="circle"
              iconSize={10}
            />
            <Tooltip />
          </ComposedChart>
        </ResponsiveContainer>
      )}
    </div>
  );
};

export default Graph;
