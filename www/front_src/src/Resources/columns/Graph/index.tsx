import * as React from 'react';

import {
  ComposedChart,
  Area,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Legend,
} from 'recharts';
import filesize from 'filesize';
import format from 'date-fns/format';

import { fade, makeStyles, CircularProgress } from '@material-ui/core';
import IconBarChart from '@material-ui/icons/BarChart';

import { useCancelTokenSource } from '@centreon/ui';

import { labelGraph } from '../../translatedLabels';
import HoverChip from '../HoverChip';
import { getData } from '../../api';
import { ColumnProps } from '..';

const graphHeight = 350;
const graphWidth = 475;

const useStyles = makeStyles((theme) => ({
  container: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: theme.palette.common.white,
    height: graphHeight,
    width: graphWidth,
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

const Graph = ({ endpoint }: Props): JSX.Element => {
  const { cancel, token } = useCancelTokenSource();
  const [graphData, setGraphData] = React.useState<GraphData>();

  const classes = useStyles();

  React.useEffect(() => {
    getData<Array<GraphData>>({
      endpoint,
      requestParams: { cancelToken: token },
    }).then((retrievedGraphData) => setGraphData(retrievedGraphData[0]));
    return (): void => cancel();
  }, []);

  const getMetricDataForTimeIndex = (timeIndex) => {
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
        <YAxis
          yAxisId={unit}
          key={unit}
          unit={unit}
          orientation={index === 0 ? 'left' : 'right'}
          tickFormatter={(tick): string => yAxisFormatter({ tick, unit })}
        />
      ))
    ) : (
      <YAxis />
    );

  const legendFormatter = (value): JSX.Element => (
    <span className={classes.legend}>{value}</span>
  );

  const loading = graphData === undefined;

  return (
    <div className={classes.container}>
      {loading ? (
        <CircularProgress size={60} color="primary" />
      ) : (
        <ComposedChart
          className={classes.graph}
          width={graphWidth}
          height={graphHeight}
          data={data}
        >
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="time" tickFormatter={xAxisFormatter} />
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
          <Legend iconType="square" formatter={legendFormatter} />
        </ComposedChart>
      )}
    </div>
  );
};

const GraphColumn = ({ Cell, row }: ColumnProps): JSX.Element => {
  return (
    <Cell width={50}>
      {row.graph_endpoint && (
        <HoverChip
          ariaLabel={labelGraph}
          Icon={(): JSX.Element => <IconBarChart />}
        >
          <Graph endpoint={row.graph_endpoint} />
        </HoverChip>
      )}
    </Cell>
  );
};

export default GraphColumn;
