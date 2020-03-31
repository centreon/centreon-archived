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

import { fade, makeStyles, Typography, Grid } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import useGet from '../../useGet';
import { formatTimeAxis } from '../format';
import getTimeSeries from './timeSeries';

const JSXXAxis = (XAxis as unknown) as (props) => JSX.Element;
const JSXYAxis = (YAxis as unknown) as (props) => JSX.Element;

const useStyles = makeStyles((theme) => ({
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
    // margin: 'auto',
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

const PerformanceGraph = ({ endpoint }: Props): JSX.Element | null => {
  const [graphData, setGraphData] = React.useState<GraphData>();

  const get = useGet({
    endpoint,
    onSuccess: setGraphData,
  });

  const classes = useStyles();

  React.useEffect(() => {
    get();
  }, []);

  if (graphData === undefined) {
    return <LoadingSkeleton />;
  }

  const hasData = graphData.times.length > 0;

  if (!hasData) {
    return null;
  }

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

  const getUnits = (): Array<string> => [
    ...new Set(graphData.metrics.map(({ unit }) => unit)),
  ];

  const yAxisFormatter = ({ tick, unit }): string =>
    unit === ''
      ? tick
      : filesize(tick, { base: getBase(unit) }).replace('B', '');

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

  return (
    <ResponsiveContainer>
      <ComposedChart className={classes.graph} data={getTimeSeries(graphData)}>
        <Legend
          formatter={legendFormatter}
          iconType="circle"
          iconSize={8}
          wrapperStyle={{ bottom: 0 }}
        />
        <CartesianGrid strokeDasharray="3 3" />
        <JSXXAxis dataKey="time" tickFormatter={formatTimeAxis} />
        {YAxes}
        {graphData?.metrics.map(({ metric, ds_data, unit }, index) =>
          ds_data.ds_filled ? (
            <Area
              key={metric}
              type="monotone"
              dataKey={metric}
              stackId={index}
              stroke={ds_data.ds_color_line}
              fill={fade(ds_data.ds_color_area, ds_data.ds_transparency * 0.01)}
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

        <Tooltip />
      </ComposedChart>
    </ResponsiveContainer>
  );
};

export default PerformanceGraph;
