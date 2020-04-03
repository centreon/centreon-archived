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
import { pipe, map, uniq, prop, isEmpty } from 'ramda';

import { fade, makeStyles, Typography } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import useGet from '../../useGet';
import { formatTimeAxis } from '../format';
import getTimeSeries from './timeSeries';
import { GraphData } from './models';

const useStyles = makeStyles((theme) => ({
  container: {
    display: 'grid',
    gridTemplateRows: '20px 1fr',
    height: '100%',
    justifyItems: 'center',
  },
  graph: {
    height: '100%',
    width: '100%',
  },
  loadingSkeleton: {
    display: 'grid',
    gridTemplateRows: '10px 1fr auto',
    gridGap: theme.spacing(1),
    paddingLeft: theme.spacing(2),
    paddingRight: theme.spacing(2),
    height: '95%',
  },
  loadingSkeletonLine: {
    transform: 'none',
    paddingBottom: theme.spacing(1),
  },
}));

interface Props {
  endpoint: string;
}

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  const skeletonLine = <Skeleton className={classes.loadingSkeletonLine} />;

  return (
    <div className={classes.loadingSkeleton}>
      {skeletonLine}
      {skeletonLine}
      {skeletonLine}
    </div>
  );
};

const PerformanceGraph = ({ endpoint }: Props): JSX.Element | null => {
  const classes = useStyles();

  const [graphData, setGraphData] = React.useState<GraphData>();

  const get = useGet({
    endpoint,
    onSuccess: setGraphData,
  });

  React.useEffect(() => {
    get();
  }, [endpoint]);

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

  const getUnits = (): Array<string> => {
    return pipe(map(prop('unit')), uniq)(graphData.metrics);
  };

  const displayMultipleYAxes = getUnits().length < 3;

  const formatYAxis = ({ tick, unit }): string =>
    isEmpty(unit)
      ? tick
      : filesize(tick, { base: getBase(unit) }).replace('B', '');

  const YAxes = displayMultipleYAxes ? (
    getUnits().map((unit, index) => (
      <YAxis
        yAxisId={unit}
        key={unit}
        unit={unit}
        orientation={index === 0 ? 'left' : 'right'}
        tickFormatter={(tick): string => formatYAxis({ tick, unit })}
      />
    ))
  ) : (
    <YAxis />
  );

  const formatLegend = (value): JSX.Element => (
    <Typography variant="caption" color="textPrimary">
      {value}
    </Typography>
  );

  return (
    <div className={classes.container}>
      <Typography variant="body2" color="textPrimary">
        {graphData.global.title}
      </Typography>
      <ResponsiveContainer className={classes.graph}>
        <ComposedChart data={getTimeSeries(graphData)}>
          <Legend
            formatter={formatLegend}
            iconType="circle"
            iconSize={8}
            wrapperStyle={{ bottom: 0 }}
          />
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="time" tickFormatter={formatTimeAxis} />
          {YAxes}
          {graphData.metrics.map(({ metric, ds_data, unit }, index) => {
            const yAxisId = displayMultipleYAxes ? unit : undefined;

            return ds_data.ds_filled ? (
              <Area
                isAnimationActive={false}
                key={metric}
                dot={false}
                dataKey={metric}
                stackId={index}
                stroke={ds_data.ds_color_line}
                fill={fade(
                  ds_data.ds_color_area,
                  ds_data.ds_transparency * 0.01,
                )}
                yAxisId={yAxisId}
              />
            ) : (
              <Line
                key={metric}
                dataKey={metric}
                stroke={ds_data.ds_color_line}
                dot={false}
                yAxisId={yAxisId}
              />
            );
          })}

          <Tooltip />
        </ComposedChart>
      </ResponsiveContainer>
    </div>
  );
};

export default PerformanceGraph;
