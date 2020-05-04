import * as React from 'react';

import {
  ComposedChart,
  Area,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
} from 'recharts';
import filesize from 'filesize';
import { pipe, map, uniq, prop, propEq, find, path } from 'ramda';

import { fade, makeStyles, Typography, Theme } from '@material-ui/core';

import { useRequest, getData } from '@centreon/ui';

import { timeFormat, dateTimeFormat } from '../format';
import { parseAndFormat } from '../../dateTime';
import getTimeSeries, { getLegend } from './timeSeries';
import { GraphData } from './models';
import { labelNoDataForThisPeriod } from '../../translatedLabels';
import LoadingSkeleton from './LoadingSkeleton';

interface Props {
  endpoint: string;
  xAxisTickFormat?: string;
  graphHeight: number;
}

const useStyles = makeStyles<Theme, Pick<Props, 'graphHeight'>>((theme) => ({
  container: {
    display: 'grid',
    flexDirection: 'column',
    gridTemplateRows: ({ graphHeight }): string => `auto ${graphHeight}px auto`,
    gridColumnGap: theme.spacing(1),
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
  legendItem: {
    display: 'flex',
    alignItems: 'center',
    marginRight: theme.spacing(1),
  },
  legendIcon: {
    width: 8,
    height: 8,
    borderRadius: '50%',
    marginRight: theme.spacing(1),
  },
}));

const PerformanceGraph = ({
  endpoint,
  graphHeight,
  xAxisTickFormat = timeFormat,
}: Props): JSX.Element | null => {
  const classes = useStyles({ graphHeight });

  const [graphData, setGraphData] = React.useState<GraphData>();

  const { sendRequest } = useRequest<GraphData>({
    request: getData,
  });

  React.useEffect(() => {
    sendRequest(endpoint).then(setGraphData);
  }, [endpoint]);

  if (graphData === undefined) {
    return <LoadingSkeleton />;
  }

  const hasData = graphData.times.length > 0;

  if (!hasData) {
    return (
      <div className={classes.noDataContainer}>
        <Typography align="center" variant="body1">
          {labelNoDataForThisPeriod}
        </Typography>
      </div>
    );
  }

  const timeSeries = getTimeSeries(graphData);
  const legend = getLegend(graphData);

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

  const formatValue = ({ value, unit }): string => {
    return filesize(value, { base: getBase(unit) }).replace('B', '');
  };

  const YAxes = displayMultipleYAxes ? (
    getUnits().map((unit, index) => (
      <YAxis
        yAxisId={unit}
        key={unit}
        orientation={index === 0 ? 'left' : 'right'}
        tickFormatter={(tick): string => formatValue({ value: tick, unit })}
        tick={{ fontSize: 12 }}
      />
    ))
  ) : (
    <YAxis tick={{ fontSize: 12 }} />
  );

  const formatTooltipValue = (value, metric, { unit }): Array<string> => {
    const legendName = pipe(
      find(propEq('metric', metric)),
      path(['name']),
    )(legend) as string;

    return [formatValue({ value, unit }), legendName];
  };

  const formatXAxisTick = (tick): string =>
    parseAndFormat({ isoDate: tick, to: xAxisTickFormat });

  const formatTooltipTime = (tick): string =>
    parseAndFormat({ isoDate: tick, to: dateTimeFormat });

  return (
    <div className={classes.container}>
      <Typography variant="body2" color="textPrimary">
        {graphData.global.title}
      </Typography>
      <ResponsiveContainer className={classes.graph}>
        <ComposedChart data={timeSeries} stackOffset="sign">
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis
            dataKey="time"
            tickFormatter={formatXAxisTick}
            tick={{ fontSize: 13 }}
          />
          {YAxes}
          {graphData.metrics.map(({ metric, ds_data, unit }) => {
            const yAxisId = displayMultipleYAxes ? unit : undefined;

            return ds_data.ds_filled ? (
              <Area
                key={metric}
                dot={false}
                dataKey={metric}
                unit={unit}
                stroke={ds_data.ds_color_line}
                fill={fade(
                  ds_data.ds_color_area,
                  ds_data.ds_transparency * 0.01,
                )}
                yAxisId={yAxisId}
                isAnimationActive={false}
              />
            ) : (
              <Line
                key={metric}
                dataKey={metric}
                unit={unit}
                stroke={ds_data.ds_color_line}
                dot={false}
                yAxisId={yAxisId}
                isAnimationActive={false}
              />
            );
          })}

          <Tooltip
            labelFormatter={formatTooltipTime}
            formatter={formatTooltipValue}
            contentStyle={{}}
          />
        </ComposedChart>
      </ResponsiveContainer>
      <div className={classes.legend}>
        {legend.map(({ color, name }) => (
          <div className={classes.legendItem} key={name}>
            <div
              className={classes.legendIcon}
              style={{ backgroundColor: color }}
            />
            <Typography align="center" variant="caption">
              {name}
            </Typography>
          </div>
        ))}
      </div>
    </div>
  );
};

export default PerformanceGraph;
